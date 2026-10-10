import HeaderClient from "./HeaderClient";
import { getMenu } from "@/services/menuService";
import { getSettings } from "@/services/settingService";

/** Server component: lấy menu header + settings rồi render header client. */
export default async function Header() {
  const [menu, settings] = await Promise.all([
    getMenu("header").catch(() => null),
    getSettings(),
  ]);

  return (
    <HeaderClient
      siteName={settings.site_name}
      hotline={settings.hotline}
      announcement={settings.announcement}
      items={menu?.items ?? []}
    />
  );
}
