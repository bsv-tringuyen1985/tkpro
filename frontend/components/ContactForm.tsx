"use client";

import { useState, type FormEvent } from "react";
import { sendContactMessage } from "@/services/contactService";
import { useToast } from "./Toast";
import { ApiError } from "@/lib/api";

type FieldErrors = Record<string, string[]>;

/** Form liên hệ: POST /contact-messages, hiển thị lỗi validation từ envelope. */
export default function ContactForm({ prefillSubject = "" }: { prefillSubject?: string }) {
  const toast = useToast();
  const [name, setName] = useState("");
  const [phone, setPhone] = useState("");
  const [email, setEmail] = useState("");
  const [subject, setSubject] = useState(prefillSubject);
  const [message, setMessage] = useState("");
  const [errors, setErrors] = useState<FieldErrors>({});
  const [sending, setSending] = useState(false);
  const [done, setDone] = useState(false);

  const fieldError = (field: string) => errors[field]?.[0];

  const inputClass = (field: string) =>
    `w-full rounded-[10px] border bg-white px-3.5 py-3 text-[13px] text-ink outline-hidden placeholder:text-muted focus:border-brand ${
      fieldError(field) ? "border-red-500" : "border-line"
    }`;

  async function onSubmit(e: FormEvent) {
    e.preventDefault();
    setErrors({});
    setSending(true);
    try {
      await sendContactMessage({ name, phone, email, subject, message });
      setDone(true);
      toast("success", "Đã gửi liên hệ thành công. TKPRO sẽ phản hồi sớm nhất!");
      setName("");
      setPhone("");
      setEmail("");
      setSubject("");
      setMessage("");
    } catch (err) {
      if (err instanceof ApiError && err.errors) {
        setErrors(err.errors);
        toast("error", err.message);
      } else if (err instanceof ApiError) {
        toast("error", err.message);
      } else {
        toast("error", "Không gửi được. Vui lòng thử lại sau.");
      }
    } finally {
      setSending(false);
    }
  }

  if (done) {
    return (
      <div className="rounded-[20px] bg-white p-8 text-center" role="status">
        <div className="mx-auto grid h-16 w-16 place-items-center rounded-full bg-brand-mint text-3xl">✓</div>
        <h3 className="mt-4 font-serif text-2xl font-bold text-ink">Cảm ơn bạn đã liên hệ!</h3>
        <p className="mx-auto mt-2 max-w-sm text-sm leading-relaxed text-muted">
          Thông tin của bạn đã được ghi nhận. Đội ngũ TKPRO sẽ gọi lại trong thời gian sớm nhất.
        </p>
        <button
          type="button"
          onClick={() => setDone(false)}
          className="mt-5 rounded-full border border-line px-5 py-2.5 text-sm font-bold text-ink hover:border-brand"
        >
          Gửi thêm yêu cầu khác
        </button>
      </div>
    );
  }

  return (
    <form onSubmit={onSubmit} className="grid gap-2.5 rounded-[20px] bg-white p-5 md:p-6" noValidate>
      <div>
        <input
          value={name}
          onChange={(e) => setName(e.target.value)}
          placeholder="Họ và tên *"
          aria-label="Họ và tên"
          className={inputClass("name")}
        />
        {fieldError("name") && <p className="mt-1 text-xs text-red-600">{fieldError("name")}</p>}
      </div>
      <div>
        <input
          value={phone}
          onChange={(e) => setPhone(e.target.value)}
          type="tel"
          placeholder="Số điện thoại *"
          aria-label="Số điện thoại"
          className={inputClass("phone")}
        />
        {fieldError("phone") && <p className="mt-1 text-xs text-red-600">{fieldError("phone")}</p>}
      </div>
      <div>
        <input
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          type="email"
          placeholder="Email"
          aria-label="Email"
          className={inputClass("email")}
        />
        {fieldError("email") && <p className="mt-1 text-xs text-red-600">{fieldError("email")}</p>}
      </div>
      <div>
        <input
          value={subject}
          onChange={(e) => setSubject(e.target.value)}
          placeholder="Thiết bị / linh kiện bạn quan tâm"
          aria-label="Thiết bị quan tâm"
          className={inputClass("subject")}
        />
        {fieldError("subject") && <p className="mt-1 text-xs text-red-600">{fieldError("subject")}</p>}
      </div>
      <div>
        <textarea
          value={message}
          onChange={(e) => setMessage(e.target.value)}
          placeholder="Nhu cầu hoặc cấu hình mong muốn"
          aria-label="Nội dung"
          rows={4}
          className={`${inputClass("message")} resize-y`}
        />
        {fieldError("message") && <p className="mt-1 text-xs text-red-600">{fieldError("message")}</p>}
      </div>
      <button
        type="submit"
        disabled={sending}
        className="flex w-full items-center justify-center gap-2 rounded-full bg-cta px-5 py-3.5 text-sm font-extrabold text-white transition hover:brightness-110 disabled:cursor-wait disabled:opacity-70"
      >
        {sending ? "Đang gửi…" : "Nhận tư vấn miễn phí →"}
      </button>
      <p className="text-center text-[11px] text-muted">Thông tin của bạn chỉ dùng để liên hệ tư vấn.</p>
    </form>
  );
}
