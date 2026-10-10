/** @type {import('next').NextConfig} */
const nextConfig = {
  outputFileTracingRoot: __dirname,
  reactStrictMode: true,
  images: {
    // Local Docker URLs belong to the browser host, not the Next container.
    unoptimized: ['localhost', '127.0.0.1', '[::1]'].includes(
      new URL(process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api/v1').hostname
    ),
    remotePatterns: [
      // Ảnh upload từ Laravel API: /storage/...
      { protocol: "http", hostname: "localhost", port: "8000", pathname: "/storage/**" },
      { protocol: "http", hostname: "127.0.0.1", port: "8000", pathname: "/storage/**" },
      { protocol: "http", hostname: "localhost", pathname: "/storage/**" },
      { protocol: "http", hostname: "127.0.0.1", pathname: "/storage/**" },
      { protocol: "https", hostname: "**", pathname: "/storage/**" },
    ],
  },
  env: {
    NEXT_PUBLIC_API_URL: process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000/api/v1",
  },
};

module.exports = nextConfig;
