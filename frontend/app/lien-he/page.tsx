import type { Metadata } from "next";
import Link from "next/link";
import Breadcrumb from "@/components/Breadcrumb";
import ContactSection from "@/features/contact/ContactSection";
import { getSettings } from "@/services/settingService";
import { getPage } from "@/services/pageService";
import { buildMetadata } from "@/lib/metadata";

export const revalidate = 300;

export async function generateMetadata(): Promise<Metadata> {
  try {
    const page = await getPage("lien-he");
    return buildMetadata(page);
  } catch {
    return { title: "Liên hệ" };
  }
}

export default async function LienHePage({
  searchParams,
}: {
  searchParams: Promise<{ [key: string]: string | string[] | undefined }>;
}) {
  const settings = await getSettings();
  const query = await searchParams;
  const sanPham =
    typeof query["san-pham"] === "string" ? query["san-pham"] : "";
  const prefill = sanPham ? `Tư vấn sản phẩm: ${sanPham}` : "";

  return (
    <div className="mx-auto max-w-[1180px] px-4 py-10 md:px-6">
      <Breadcrumb items={[{ label: "Liên hệ" }]} />
      <ContactSection
        settings={settings}
        prefillSubject={prefill}
        prefillProductName={sanPham}
        headingLevel="h1"
      />
      <p className="mt-8 text-center text-xs text-muted">
        Hoặc gọi trực tiếp hotline{" "}
        <Link href={`tel:${settings.hotline.replace(/\s/g, "")}`} className="font-bold text-brand">
          {settings.hotline}
        </Link>{" "}
        để được tư vấn ngay.
      </p>
    </div>
  );
}
