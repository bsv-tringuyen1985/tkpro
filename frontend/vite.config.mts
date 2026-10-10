import { defineConfig } from "vite";
import { fileURLToPath } from "node:url";
import { readFileSync } from "node:fs";

const file = (path: string) => fileURLToPath(new URL(path, import.meta.url));

export default defineConfig({
  root: file("./static"),
  publicDir: file("./public"),
  base: "/",
  resolve: {
    alias: [
      { find: /^next\/link$/, replacement: file("./static/Link.tsx") },
      { find: /^next\/image$/, replacement: file("./static/Image.tsx") },
      { find: /^next\/navigation$/, replacement: file("./static/navigation.ts") },
      { find: "@", replacement: file("./") },
    ],
  },
  define: {
    "process.env.NEXT_PUBLIC_API_URL": JSON.stringify("/api/v1"),
    "process.env.NEXT_PUBLIC_SITE_URL": JSON.stringify(""),
    "process.env.API_INTERNAL_URL": JSON.stringify(""),
  },
  build: { outDir: file("./out"), emptyOutDir: true },
  plugins: [{
    name: "tkpro-static-hosting",
    generateBundle() {
      this.emitFile({ type: "asset", fileName: ".htaccess", source: readFileSync(file("./static/hosting/.htaccess"), "utf8") });
      this.emitFile({ type: "asset", fileName: "robots.txt", source: "User-agent: *\nAllow: /\nDisallow: /tim-kiem\n" });
    },
  }],
});
