# -----------------------------------------------------------------------------
# TKPRO nginx — reverse proxy duy nhất phía browser
#   /            → frontend:3000
#   /api/*       → api:8000        (giữ nguyên path /api/...)
#   /admin/*     → cms:8001        (giữ nguyên path /admin/...; cms tự xử lý prefix)
#   /storage/*   → api:8000/storage/*
#   /sitemap.xml, /robots.txt → api:8000 (route web của api)
# -----------------------------------------------------------------------------
FROM nginx:1.27-alpine

COPY ./default.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
