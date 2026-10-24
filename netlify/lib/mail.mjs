// E-maily z webu: upozornění na novou poptávku a potvrzení zákazníkovi.
// Odesílá se přes SMTP schránky na Seznamu (SMTP_PASS), nebo přes Resend (RESEND_API_KEY).
import nodemailer from 'nodemailer';

const env = (k) => process.env[k] || '';
const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

const SITE = () => (env('SITE_URL') || env('URL') || 'https://create4you.cz').replace(/\/$/, '');
const FONT = "font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif";
const C = { bg: '#f1f0ec', card: '#ffffff', ink: '#121216', muted: '#6b6b76', line: '#e6e4de', lime: '#c6f432', violet: '#4c2bff', dark: '#121216' };

export const mailConfigured = () => Boolean(env('SMTP_PASS') || env('RESEND_API_KEY'));

export async function sendMail({ to, subject, html, text, replyTo }) {
  const from = env('MAIL_FROM') || `create4you <${env('SMTP_USER') || 'info@create4you.cz'}>`;
  if (env('SMTP_PASS')) {
    const transport = nodemailer.createTransport({
      host: env('SMTP_HOST') || 'smtp.seznam.cz',
      port: Number(env('SMTP_PORT') || 465),
      secure: Number(env('SMTP_PORT') || 465) === 465,
      auth: { user: env('SMTP_USER') || 'info@create4you.cz', pass: env('SMTP_PASS') },
    });
    return transport.sendMail({ from, to, subject, html, text, replyTo });
  }
  const res = await fetch('https://api.resend.com/emails', {
    method: 'POST',
    headers: { Authorization: `Bearer ${env('RESEND_API_KEY')}`, 'Content-Type': 'application/json' },
    body: JSON.stringify({ from, to: [].concat(to), subject, html, text, reply_to: replyTo }),
  });
  if (!res.ok) throw new Error(`Resend ${res.status}: ${await res.text()}`);
  return res.json();
}

// Společný rámec: světlé pozadí, karta s logem, patička
const layout = ({ preheader, body, footer }) => `<!doctype html>
<html lang="cs"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="color-scheme" content="light"><meta name="supported-color-schemes" content="light"><title>create4you</title></head>
<body style="margin:0;padding:0;background:${C.bg};${FONT}">
<div style="display:none;max-height:0;overflow:hidden;opacity:0">${esc(preheader)}</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:${C.bg}"><tr><td align="center" style="padding:28px 12px">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px">
  <tr><td style="padding:0 4px 18px 4px">
    <a href="${SITE()}" style="text-decoration:none"><img src="${SITE()}/assets/img/email/logo.png" width="150" height="32" alt="create4you" style="display:block;border:0;width:150px;height:auto"></a>
  </td></tr>
  <tr><td style="background:${C.card};border-radius:20px;border:1px solid ${C.line};overflow:hidden">${body}</td></tr>
  <tr><td style="padding:18px 8px 0 8px;color:${C.muted};font-size:12px;line-height:1.6;text-align:center">${footer}</td></tr>
</table></td></tr></table></body></html>`;

const button = (href, label, primary = true) =>
  `<a href="${esc(href)}" style="display:inline-block;margin:0 8px 10px 0;padding:12px 20px;border-radius:999px;font-weight:700;font-size:14px;text-decoration:none;${primary ? `background:${C.lime};color:${C.ink}` : `background:${C.card};color:${C.ink};border:1px solid ${C.line}`}">${label}</a>`;

const row = (label, value) => value ? `<tr>
  <td style="padding:10px 0;border-top:1px solid ${C.line};color:${C.muted};font-size:13px;width:120px;vertical-align:top">${label}</td>
  <td style="padding:10px 0;border-top:1px solid ${C.line};color:${C.ink};font-size:15px;font-weight:600">${value}</td></tr>` : '';

const chips = (list) => list.map((s) =>
  `<span style="display:inline-block;margin:0 6px 6px 0;padding:5px 12px;border-radius:999px;background:#eeeaff;color:${C.violet};font-size:13px;font-weight:600">${esc(s)}</span>`).join('');

const tel = (p) => String(p).replace(/[^\d+]/g, '');

export function inquiryEmail(d, { id } = {}) {
  const services = d.services ? d.services.split(', ').filter(Boolean) : [];
  const when = new Date().toLocaleString('cs-CZ', { timeZone: 'Europe/Prague', day: 'numeric', month: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
  const subjectLine = `Re: poptávka z webu create4you.cz`;
  const body = `
  <div style="background:${C.dark};padding:28px 28px 26px 28px">
    <span style="display:inline-block;padding:5px 12px;border-radius:999px;background:${C.lime};color:${C.ink};font-size:12px;font-weight:800;letter-spacing:.06em;text-transform:uppercase">Nová poptávka</span>
    <h1 style="margin:16px 0 4px 0;color:#ffffff;font-size:28px;line-height:1.2;letter-spacing:-.02em">${esc(d.name)}</h1>
    <p style="margin:0;color:#b9b9c4;font-size:15px">${d.company ? `${esc(d.company)} · ` : ''}${esc(when)}</p>
  </div>
  <div style="padding:24px 28px 8px 28px">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
      ${row('E-mail', `<a href="mailto:${esc(d.email)}" style="color:${C.violet};text-decoration:none">${esc(d.email)}</a>`)}
      ${row('Telefon', d.phone ? `<a href="tel:${esc(tel(d.phone))}" style="color:${C.violet};text-decoration:none">${esc(d.phone)}</a>` : '')}
      ${row('Firma', esc(d.company))}
      ${row('Rozpočet', esc(d.budget))}
    </table>
    ${services.length ? `<p style="margin:18px 0 8px 0;color:${C.muted};font-size:13px">Má zájem o</p><div>${chips(services)}</div>` : ''}
    ${d.message ? `<p style="margin:18px 0 8px 0;color:${C.muted};font-size:13px">Zpráva</p>
    <div style="background:#f7f6f2;border-left:4px solid ${C.lime};border-radius:12px;padding:16px 18px;color:${C.ink};font-size:15px;line-height:1.6">${esc(d.message).replace(/\n/g, '<br>')}</div>` : ''}
  </div>
  <div style="padding:22px 28px 18px 28px">
    ${button(`mailto:${d.email}?subject=${encodeURIComponent(subjectLine)}`, 'Odpovědět e-mailem')}
    ${d.phone ? button(`tel:${tel(d.phone)}`, `Zavolat ${esc(d.phone)}`, false) : ''}
    ${button(`${SITE()}/admin/#poptavky${id ? `/${id}` : ''}`, 'Otevřít v administraci', false)}
  </div>`;
  const text = `Nová poptávka z webu create4you.cz (${when})\n\nJméno: ${d.name}\nFirma: ${d.company}\nE-mail: ${d.email}\nTelefon: ${d.phone}\n`
    + `Zájem o: ${d.services}\nRozpočet: ${d.budget}\n\nZpráva:\n${d.message}\n\nAdministrace: ${SITE()}/admin/#poptavky`;
  return {
    subject: `Nová poptávka: ${d.name}${d.company ? ` (${d.company})` : ''}`,
    html: layout({ preheader: `${d.services || 'Poptávka'} · ${d.email}`, body, footer: `Poptávka z formuláře na <a href="${SITE()}" style="color:${C.muted}">create4you.cz</a>. Stačí odpovědět na tento e-mail, odpověď půjde přímo zákazníkovi.` }),
    text,
  };
}

// Potvrzení zákazníkovi. Záměrně neobsahuje nic z toho, co zákazník napsal,
// aby formulář nešel zneužít k rozesílání cizího obsahu na libovolné adresy.
export function confirmationEmail({ hours = '24', contact = {} } = {}) {
  const phone = contact.phone || '+420 775 241 298';
  const email = contact.email || 'info@create4you.cz';
  const name = contact.name || 'Daniel Peterek';
  const step = (n, title, sub) => `<tr>
    <td style="width:44px;padding:0 0 16px 0;vertical-align:top"><span style="display:inline-block;width:32px;height:32px;line-height:32px;border-radius:50%;background:${C.lime};color:${C.ink};text-align:center;font-weight:800;font-size:14px">${n}</span></td>
    <td style="padding:4px 0 16px 0;vertical-align:top"><b style="color:${C.ink};font-size:15px">${title}</b><br><span style="color:${C.muted};font-size:14px;line-height:1.5">${sub}</span></td></tr>`;
  const body = `
  <div style="background:${C.dark};padding:32px 28px 28px 28px">
    <span style="display:inline-block;padding:5px 12px;border-radius:999px;background:${C.lime};color:${C.ink};font-size:12px;font-weight:800;letter-spacing:.06em;text-transform:uppercase">Poptávka přijata</span>
    <h1 style="margin:16px 0 8px 0;color:#ffffff;font-size:28px;line-height:1.2;letter-spacing:-.02em">Děkujeme, máme ji.</h1>
    <p style="margin:0;color:#b9b9c4;font-size:16px;line-height:1.5">Ozveme se vám do <b style="color:${C.lime}">${esc(hours)} hodin</b> v pracovní dny.</p>
  </div>
  <div style="padding:28px 28px 8px 28px">
    <p style="margin:0 0 18px 0;color:${C.muted};font-size:13px;font-weight:700;letter-spacing:.06em;text-transform:uppercase">Co bude následovat</p>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
      ${step(1, 'Projdeme vaši poptávku', 'Podíváme se na vaše zadání, případně na současný web a konkurenci.')}
      ${step(2, 'Ozveme se a domluvíme detaily', 'Krátký telefonát nebo schůzka, upřesníme cíle, termín a rozpočet.')}
      ${step(3, 'Pošleme konkrétní nabídku', 'Přesná cena a harmonogram. Žádné skryté poplatky.')}
    </table>
  </div>
  <div style="margin:4px 28px 28px 28px;padding:18px 20px;border:1px solid ${C.line};border-radius:16px;background:#f7f6f2">
    <p style="margin:0 0 2px 0;color:${C.ink};font-size:16px;font-weight:700">${esc(name)}</p>
    <p style="margin:0 0 10px 0;color:${C.muted};font-size:13px">create4you · weby, aplikace a e-shopy na míru</p>
    <p style="margin:0;font-size:14px;line-height:1.8">
      <a href="tel:${esc(tel(phone))}" style="color:${C.ink};font-weight:700;text-decoration:none">${esc(phone)}</a><br>
      <a href="mailto:${esc(email)}" style="color:${C.violet};text-decoration:none">${esc(email)}</a>
    </p>
  </div>
  <div style="padding:0 28px 22px 28px">
    ${button(`${SITE()}/#reference`, 'Prohlédnout naše reference')}
  </div>`;
  return {
    subject: 'Děkujeme za poptávku · create4you',
    html: layout({ preheader: `Ozveme se do ${hours} hodin. Mezitím se můžete podívat na naše reference.`, body, footer: `Tento e-mail jsme poslali, protože jste vyplnili poptávku na <a href="${SITE()}" style="color:${C.muted}">create4you.cz</a>.<br>Pokud jste to nebyli vy, e-mail prosím ignorujte.` }),
    text: `Děkujeme za poptávku!\n\nOzveme se vám do ${hours} hodin v pracovní dny.\n\nCo bude následovat:\n1. Projdeme vaši poptávku\n2. Ozveme se a domluvíme detaily\n3. Pošleme konkrétní nabídku\n\n${name}\ncreate4you · ${phone} · ${email}\n${SITE()}`,
  };
}
