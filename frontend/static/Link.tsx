import type { ComponentProps } from "react";

/** Plain anchors also preserve middle-click, downloads, hashes and browser history. */
export default function Link(props: ComponentProps<"a">) {
  return <a {...props} />;
}
