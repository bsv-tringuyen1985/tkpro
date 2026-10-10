import type { ComponentProps } from "react";

type Props = ComponentProps<"img"> & { fill?: boolean; priority?: boolean };

/** No /_next/image server is required; the browser loads the original asset. */
export default function Image({ fill, priority, style, loading, ...props }: Props) {
  return <img {...props} loading={priority ? "eager" : loading || "lazy"}
    fetchPriority={priority ? "high" : undefined} decoding="async"
    style={fill ? { position: "absolute", inset: 0, width: "100%", height: "100%", ...style } : style} />;
}
