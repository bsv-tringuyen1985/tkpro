# -----------------------------------------------------------------------------
# TKPRO Frontend — Next.js (multi-stage)
#   Build/run cùng Debian để native dependencies tương thích.
#   Node 22 chạy `next start` trên port 3000.
# -----------------------------------------------------------------------------
FROM node:22-bookworm-slim AS builder

WORKDIR /app

# NEXT_PUBLIC_* phải có mặt lúc build (Next.js nhúng vào bundle browser).
# Mặc định khớp contract cho docker (qua nginx): http://localhost/api/v1
ARG NEXT_PUBLIC_API_URL=http://localhost/api/v1
ENV NEXT_PUBLIC_API_URL=${NEXT_PUBLIC_API_URL}
ARG NEXT_PUBLIC_SITE_URL=http://localhost
ENV NEXT_PUBLIC_SITE_URL=${NEXT_PUBLIC_SITE_URL}

COPY ./frontend/package*.json ./
RUN npm ci --no-audit --no-fund

COPY ./frontend/ ./
RUN npm run build

# -----------------------------------------------------------------------------
FROM node:22-bookworm-slim AS runner

WORKDIR /app
ENV NODE_ENV=production
ENV PORT=3000

# Dùng standalone output nếu có (nhẹ hơn), ngược lại copy toàn bộ .next
COPY --from=builder /app/package*.json ./
COPY --from=builder /app/node_modules ./node_modules
COPY --from=builder /app/.next ./.next
COPY --from=builder /app/public ./public
COPY --from=builder /app/next.config.js ./next.config.js

# URL nội bộ cho server components (gọi api qua mạng docker, fallback sang public)
ENV API_INTERNAL_URL=http://api:8000/api/v1
# NEXT_PUBLIC_API_URL đã được nhúng lúc build; giữ lại cho runtime reference
ARG NEXT_PUBLIC_API_URL=http://localhost/api/v1
ENV NEXT_PUBLIC_API_URL=${NEXT_PUBLIC_API_URL}
ARG NEXT_PUBLIC_SITE_URL=http://localhost
ENV NEXT_PUBLIC_SITE_URL=${NEXT_PUBLIC_SITE_URL}

EXPOSE 3000

CMD ["npm", "run", "start", "--", "-p", "3000"]
