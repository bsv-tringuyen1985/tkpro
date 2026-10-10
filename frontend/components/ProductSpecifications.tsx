import type { Product } from "@/types";

export default function ProductSpecifications({ specs }: { specs: Product["technical_specifications"] }) {
  if (!specs) return null;
  if (typeof specs === "string") {
    const text = specs.trim();
    if (!text) return null;
    if (/<[a-z][\s\S]*>/i.test(text)) {
      return <div className="prose-tkpro" dangerouslySetInnerHTML={{ __html: text }} />;
    }
    return <p className="whitespace-pre-line text-sm leading-relaxed">{text}</p>;
  }
  const entries = Array.isArray(specs)
    ? specs.map(({ label, value }) => [label, value])
    : Object.entries(specs);
  if (entries.length === 0) return null;
  return (
    <table className="spec-table">
      <tbody>
        {entries.map(([label, value], index) => (
          <tr key={`${label}-${index}`}>
            <th scope="row">{label}</th>
            <td>{value}</td>
          </tr>
        ))}
      </tbody>
    </table>
  );
}
