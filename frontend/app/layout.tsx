import type { Metadata } from "next";
import "./globals.css";
import Header from "@/components/Header";
import Footer from "@/components/Footer";
import { ToastProvider } from "@/components/Toast";
import { getSettings } from "@/services/settingService";

export async function generateMetadata(): Promise<Metadata> {
  const s = await getSettings().catch(() => null);
  const siteName = s?.site_name ?? "TKPRO";
  return {
    metadataBase: new URL(process.env.NEXT_PUBLIC_SITE_URL || "http://localhost:3000"),
    title: {
      default: `${siteName} — Điện tử · Camera · Linh kiện máy tính`,
      template: `%s | ${siteName}`,
    },
    description:
      "TKPRO — thiết bị điện tử, camera an ninh và linh kiện máy tính chính hãng. Tư vấn đúng nhu cầu, lắp đặt chuẩn, bảo hành rõ ràng.",
    keywords: ["TKPRO", "camera giám sát", "linh kiện PC", "thiết bị điện tử", "máy tính"],
    openGraph: {
      type: "website",
      locale: "vi_VN",
      siteName,
    },
    robots: { index: true, follow: true },
  };
}

export default function RootLayout({ children }: { children: React.ReactNode }) {
  const orgJsonLd = {
    "@context": "https://schema.org",
    "@type": "Organization",
    name: "TKPRO",
    description: "Thiết bị điện tử, camera và linh kiện máy tính chính hãng.",
    url: process.env.NEXT_PUBLIC_SITE_URL || "http://localhost:3000",
  };

  return (
    <html lang="vi">
      <body>
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: JSON.stringify(orgJsonLd) }}
        />
        <ToastProvider>
          <Header />
          <main>{children}</main>
          <Footer />
        </ToastProvider>
      </body>
    </html>
  );
}
