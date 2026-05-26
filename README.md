# INF781 — LaravelCaptcha

**Defensa contra bots y abuso automatizado con CAPTCHA en Laravel 13**

Proyecto de laboratorio de la materia **INF781 — Seguridad de Software** de la **Universidad Autónoma Tomás Frías (Potosí, Bolivia)**. Implementa tres mecanismos CAPTCHA distintos sobre formularios sensibles: reCAPTCHA v2, mews/captcha local y honeypot con rate limiting.

## Formularios protegidos

| Formulario | Mecanismo | Tipo |
|---|---|---|
| Registro | Google reCAPTCHA v2 | Checkbox "No soy un robot" + verificación server-side |
| Login | mews/captcha | Imagen distorsionada local (sin dependencia externa) |
| Contacto | Honeypot + Rate Limiting | Campo oculto CSS + 5 intentos/minuto por IP |

## Requisitos

- PHP 8.3+ con extensiones `gd`, `mbstring`, `pdo_pgsql`, `openssl`, `curl`
- Composer 2.7+
- Node.js 20+ con npm
- PostgreSQL 15+
- Cuenta de Google Cloud para claves reCAPTCHA

## Instalación

```bash
# 1. Clonar el repositorio
git clone https://github.com/BazilyCcherenkov/INF781--LaravelCaptcha.git
cd INF781--LaravelCaptcha

# 2. Instalar dependencias PHP
composer install

# 3. Instalar dependencias frontend
npm install && npm run build

# 4. Configurar variables de entorno
cp .env.example .env
php artisan key:generate

# 5. Crear base de datos y usuario PostgreSQL
sudo -u postgres psql -c "CREATE USER inf781_user WITH PASSWORD 'tu_password_seguro';"
sudo -u postgres psql -c "CREATE DATABASE inf781_captcha OWNER inf781_user;"

# 6. Editar .env con tus credenciales
#    DB_USERNAME=inf781_user
#    DB_PASSWORD=tu_password_seguro

# 7. Ejecutar migraciones
php artisan migrate

# 8. Iniciar servidor de desarrollo
php artisan serve
```

## Configurar reCAPTCHA v2

1. Ve a [https://www.google.com/recaptcha/admin](https://www.google.com/recaptcha/admin)
2. Crea un sitio nuevo → reCAPTCHA v2 → "No soy un robot" (Casilla)
3. Agrega los dominios `localhost` y `127.0.0.1`
4. Copia las claves en tu `.env`:

```bash
RECAPTCHA_SITE_KEY=6Lc...tu_site_key
RECAPTCHA_SECRET_KEY=6Lc...tu_secret_key
```

> ⚠ La Secret Key nunca debe aparecer en código frontend ni en commits.

## Ejecutar tests

```bash
php artisan test

# Tests específicos de CAPTCHA:
php artisan test --filter=CaptchaProtectionTest
```

Los tests validan:
- Rechazo de registro sin token reCAPTCHA
- Rechazo de login sin código CAPTCHA
- Rechazo silencioso de bots en formulario de contacto (honeypot)

## Capturas

### Formulario de registro — reCAPTCHA v2
![Registro con reCAPTCHA v2](https://via.placeholder.com/600x400?text=Captura:+Registro+reCAPTCHA+v2)

### Formulario de login — mews/captcha
![Login con CAPTCHA local](https://via.placeholder.com/600x400?text=Captura:+Login+mews+captcha)

### Formulario de contacto — Honeypot + Rate Limiting
![Contacto con honeypot](https://via.placeholder.com/600x400?text=Captura:+Contacto+Honeypot)

## Análisis crítico

### 1. Amenazas mitigadas y residuales

Cada formulario protegido mitiga amenazas específicas:
- **Registro (reCAPTCHA v2)**: Evita registro masivo de cuentas falsas (Sybil attacks) y automatización de creación de usuarios.
- **Login (mews/captcha)**: Dificulta ataques de fuerza bruta y credential stuffing al añadir una barrera visual por intento.
- **Contacto (honeypot + rate limiting)**: Filtra bots rudimentarios (honeypot) y limita el spam volumétrico (rate limiting).

**Amenaza residual**: Ningún CAPTCHA es infalible. Las granjas humanas (click farms) pueden resolver CAPTCHAs manualmente por centavos. Modelos de visión por computadora modernos (OCR con deep learning) rompen CAPTCHAs de texto distorsionado. Los ataques distribuidos (botnets) evaden rate limiting rotando IPs.

### 2. Comparativa: reCAPTCHA v2 vs mews/captcha

| Dimensión | reCAPTCHA v2 (Google) | mews/captcha |
|---|---|---|
| **Seguridad** | Alta — análisis comportamental + reputación de IP | Media — vulnerable a OCR moderno |
| **Accesibilidad** | Incluye alternativa de audio | Limitada — solo imagen visual |
| **Privacidad** | Google rastrea cookies, historial y comportamiento | Sin rastreo externo — 100% local |
| **Dependencia externa** | Requiere conexión a servidores de Google | Cero dependencia — funciona offline |
| **Experiencia de usuario** | Fluida — solo un clic | Fricción — escribir texto distorsionado |

**¿Cuándo preferir cada uno?** reCAPTCHA v2 es superior en entornos públicos con alta exposición a bots. mews/captcha es preferible en intranets, proyectos con restricciones de privacidad (GDPR, soberanía de datos) o cuando no hay conectividad externa.

### 3. Evasión de CAPTCHA y defensas complementarias

**Formas conocidas de elusión:**
1. **OCR avanzado**: Modelos CNN/RNN como Google Tesseract resuelven CAPTCHAs de texto con alta precisión. Los CAPTCHAs locales (mews/captcha) son particularmente vulnerables.
2. **Granjas humanas**: Servicios como 2Captcha resuelven cualquier CAPTCHA por ~0.50 USD por 1000 resoluciones, haciendo económicamente viable eludir la protección.

**Defensas adicionales:**
- **Rate limiting por IP y usuario** (ya implementado en contacto y login nativo de Laravel)
- **Autenticación multifactor (MFA)** como segunda capa después del login
- **Análisis de comportamiento**: Tiempo entre pulsaciones, movimiento de ratón, patrón de navegación
- **Proof-of-Work**: Soluciones como Cloudflare Turnstile o Friendly Captcha que requieren que el cliente resuelva un problema computacional

### 4. Privacidad y GDPR

reCAPTCHA de Google introduce problemas graves de privacidad. Google recopila:
- Cookies de terceros y datos de navegación
- Reputación de IP y señales de dispositivo
- Historial de interacciones con reCAPTCHA en múltiples sitios

Esto conflictúa con el Reglamento General de Protección de Datos (GDPR) de la UE, que requiere consentimiento explícito para la transferencia de datos a terceros. El Tribunal de Justicia de la Unión Europea ha cuestionado la legalidad de reCAPTCHA bajo el principio de minimización de datos (Art. 5 GDPR). Alternativas como hCaptcha o Turnstile respetan más la privacidad, mientras que mews/captcha elimina completamente el rastreo.

## Licencia

MIT License

## Autor

**BazilyCcherenkov** — INF781 — Seguridad de Software
Universidad Autónoma Tomás Frías — Potosí, Bolivia

Docente: M. Sc. Huáscar Fedor Gonzales Guzmán
