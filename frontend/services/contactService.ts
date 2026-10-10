import { apiPost } from "@/lib/api";
import type { ContactMessageInput } from "@/types";

/** POST /api/v1/contact-messages — gửi liên hệ (rate-limit 10/phút). */
export async function sendContactMessage(input: ContactMessageInput) {
  return apiPost<unknown>(`/contact-messages`, {
    name: input.name,
    phone: input.phone,
    email: input.email || undefined,
    subject: input.subject || undefined,
    interest: input.interest || undefined,
    message: input.message || undefined,
  });
}
