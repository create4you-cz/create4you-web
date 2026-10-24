// Kontaktní formulář: ochrana proti spamu → uložení poptávky do Supabase → e-mail nám + potvrzení zákazníkovi
import { mailConfigured, sendMail, inquiryEmail, confirmationEmail } from '../lib/mail.mjs';

const env = (k) => process.env[k] || '';

const reply = (req, ok, message, status = 200) => {
  if (!(req.headers.get('accept') || '').includes('application/json')) {
    // Odeslání bez JavaScriptu: zpět na formulář
    return new Response(null, { status: 303, headers: { Location: '/#kontakt' } });
  }
  return new Response(JSON.stringify({ ok, message }), { status, headers: { 'content-type': 'application/json; charset=utf-8' } });
};

const db = (path, init = {}) => fetch(`${env('SUPABASE_URL')}/rest/v1/${path}`, {
  ...init,
  headers: {
    apikey: env('SUPABASE_SERVICE_ROLE_KEY'),
    Authorization: `Bearer ${env('SUPABASE_SERVICE_ROLE_KEY')}`,
    'Content-Type': 'application/json',
    ...(init.headers || {}),
  },
});

export default async (req, context) => {
  if (req.method !== 'POST') return reply(req, false, 'Metoda není povolena.', 405);
  if (!env('SUPABASE_URL') || !env('SUPABASE_SERVICE_ROLE_KEY')) return reply(req, false, 'Formulář zatím není nastavený. Napište nám prosím e-mail.', 500);

  let form;
  try {
    form = await req.formData();
  } catch {
    return reply(req, false, 'Formulář se nepodařilo zpracovat.', 400);
  }
  const clean = (k, max) => String(form.get(k) ?? '').replace(/<[^>]*>/g, '').trim().slice(0, max);

  // Ochrana proti spamu: skryté pole + minimální doba vyplnění (roboty tváříme jako úspěch)
  const ts = parseInt(form.get('ts'), 10) || 0;
  if (clean('website', 200) || Date.now() / 1000 - ts < 3) return reply(req, true, 'Děkujeme, poptávka byla odeslána.');

  const data = {
    name: clean('name', 120),
    email: clean('email', 160),
    phone: clean('phone', 40),
    company: clean('company', 160),
    budget: clean('budget', 40),
    message: clean('message', 5000),
    services: form.getAll('services[]').slice(0, 8).map((s) => String(s).replace(/<[^>]*>/g, '').slice(0, 40)).join(', '),
    ip: context?.ip || req.headers.get('x-nf-client-connection-ip') || '',
  };
  if (!data.name || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
    return reply(req, false, 'Vyplňte prosím jméno a platný e-mail.', 422);
  }

  // Limit: nejvýš 5 poptávek z jedné IP adresy za hodinu
  if (data.ip) {
    const since = new Date(Date.now() - 3600e3).toISOString();
    const recent = await db(`inquiries?select=id&ip=eq.${encodeURIComponent(data.ip)}&created_at=gte.${encodeURIComponent(since)}`);
    if (recent.ok && (await recent.json()).length >= 5) {
      return reply(req, false, 'Odeslali jste už několik poptávek. Zkuste to prosím později, nebo nám napište e-mail.', 429);
    }
  }

  const saved = await db('inquiries', { method: 'POST', body: JSON.stringify(data), headers: { Prefer: 'return=representation' } });
  if (!saved.ok) {
    console.error('Uložení poptávky selhalo', saved.status, await saved.text());
    return reply(req, false, 'Odeslání se nepovedlo. Napište nám prosím přímo e-mail.', 500);
  }
  const id = (await saved.json().catch(() => []))[0]?.id;

  const settings = {};
  const s = await db('settings?select=key,value&key=in.(stat_response,contact_email,contact_phone,contact_name)');
  if (s.ok) for (const { key, value } of await s.json()) settings[key] = value;
  const hours = settings.stat_response || '24';

  // E-maily: upozornění nám (odpověď jde rovnou zákazníkovi) a potvrzení zákazníkovi
  if (mailConfigured()) {
    const notify = inquiryEmail(data, { id });
    const confirm = confirmationEmail({ hours, contact: { email: settings.contact_email, phone: settings.contact_phone, name: settings.contact_name } });
    const results = await Promise.allSettled([
      sendMail({ to: (env('NOTIFY_EMAIL') || settings.contact_email || 'info@create4you.cz').split(',').map((x) => x.trim()), replyTo: data.email, ...notify }),
      sendMail({ to: data.email, replyTo: settings.contact_email || undefined, ...confirm }),
    ]);
    results.forEach((r, i) => r.status === 'rejected' && console.error(i ? 'Potvrzení zákazníkovi se nepodařilo odeslat' : 'Upozornění na poptávku se nepodařilo odeslat', r.reason));
  }

  return reply(req, true, `Děkujeme! Poptávka dorazila, ozveme se do ${hours} hodin.`);
};
