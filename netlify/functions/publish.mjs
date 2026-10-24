// Zveřejnění změn z administrace: ověří přihlášeného administrátora a spustí nové sestavení webu na Netlify
const env = (k) => process.env[k] || '';
const json = (ok, message, status = 200) =>
  new Response(JSON.stringify({ ok, message }), { status, headers: { 'content-type': 'application/json; charset=utf-8' } });

export default async (req) => {
  if (req.method !== 'POST') return json(false, 'Metoda není povolena.', 405);
  const token = (req.headers.get('authorization') || '').replace(/^Bearer\s+/i, '');
  if (!token) return json(false, 'Nejste přihlášeni.', 401);

  // Kdo volá? (ověří platnost přihlášení u Supabase)
  const userRes = await fetch(`${env('SUPABASE_URL')}/auth/v1/user`, {
    headers: { apikey: env('SUPABASE_ANON_KEY') || env('SUPABASE_SERVICE_ROLE_KEY'), Authorization: `Bearer ${token}` },
  });
  if (!userRes.ok) return json(false, 'Přihlášení vypršelo. Přihlaste se prosím znovu.', 401);
  const user = await userRes.json();

  // Je to administrátor?
  const adminRes = await fetch(`${env('SUPABASE_URL')}/rest/v1/admins?select=user_id&user_id=eq.${encodeURIComponent(user.id)}`, {
    headers: { apikey: env('SUPABASE_SERVICE_ROLE_KEY'), Authorization: `Bearer ${env('SUPABASE_SERVICE_ROLE_KEY')}` },
  });
  if (!adminRes.ok || !(await adminRes.json()).length) return json(false, 'K této akci nemáte oprávnění.', 403);

  if (!env('BUILD_HOOK_URL')) return json(false, 'Na Netlify chybí nastavení BUILD_HOOK_URL.', 500);
  const hook = await fetch(env('BUILD_HOOK_URL'), { method: 'POST', body: '{}' });
  if (!hook.ok) return json(false, 'Aktualizaci webu se nepodařilo spustit.', 502);
  return json(true, 'Web se aktualizuje – změny budou vidět asi za minutu.');
};
