# HTTPS & URL Security Configuration

This document outlines the HTTPS and URL security configuration for the SWMT (Spatial Working Memory Test) application.

## Current Status

✅ **All blade templates** use Laravel helpers for URL generation:
- `route()` for named routes
- `url()` for URL generation
- `asset()` for static assets

✅ **All controllers** use `redirect()->route()` instead of hardcoded URLs

✅ **AppServiceProvider** configured to force HTTPS in production

## Configuration

### Production Setup (Automatically Enforced)

In **production environment** (`APP_ENV=production`), the application automatically:
1. Forces all URLs to use HTTPS scheme
2. Generates secure URLs for all routes and assets
3. Sends secure headers for SSL/TLS

This is configured in `app/Providers/AppServiceProvider.php`:

```php
public function boot(): void
{
    if (env('APP_ENV') === 'production') {
        URL::forceScheme('https');
    }
}
```

### Environment Variable Configuration

**Development** (`.env`):
```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
```

**Production** (`.env.production`):
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://swmtapp-production.up.railway.app
GOOGLE_REDIRECT_URL=https://swmtapp-production.up.railway.app/auth/google/callback
```

## URL Generation Best Practices

### ✅ Correct Usage

**In Blade Templates:**
```blade
<!-- Named routes -->
<form action="{{ route('student.register.process') }}" method="post">

<!-- URL generation -->
<a href="{{ url('/dashboard') }}">Dashboard</a>

<!-- Static assets -->
<img src="{{ asset('images/logo.png') }}" alt="Logo">

<!-- Secure assets (uses https in production) -->
<link rel="stylesheet" href="{{ secure_asset('css/app.css') }}">
```

**In Controllers:**
```php
// Use route() helper for redirects
return redirect()->route('student.login');

// Use url() for dynamic URLs
return redirect(url('/dashboard'));

// Avoid hardcoded URLs
// ❌ DO NOT: return redirect('http://localhost/...');
// ❌ DO NOT: return redirect('https://domain.com/...');
```

### ❌ Incorrect Usage (Avoided)

```blade
<!-- Hardcoded HTTP -->
<form action="http://localhost:8000/register">

<!-- Hardcoded URLs -->
<a href="http://domain.com/page">

<!-- Hardcoded assets -->
<img src="http://domain.com/images/logo.png">
```

## Google OAuth Configuration

The Google OAuth redirect URL is configured separately in `.env`:

```env
GOOGLE_REDIRECT_URL=https://swmtapp-production.up.railway.app/auth/google/callback
```

This ensures Google redirects to the correct HTTPS endpoint after authentication.

## Mixed Content Prevention

✅ All resources load via HTTPS in production
✅ No hardcoded HTTP URLs
✅ No mixed content warnings in browser console

To verify:
1. Check browser console for mixed content warnings
2. Inspect security headers in browser DevTools
3. Test with HTTPS in production

## Deployment Checklist

Before deploying to production:

- [ ] Set `APP_ENV=production` in production `.env`
- [ ] Set `APP_DEBUG=false` in production `.env`
- [ ] Set `APP_URL=https://yourdomain.com` in production `.env`
- [ ] Configure SSL/TLS certificate (handled by Railway automatically)
- [ ] Update `GOOGLE_REDIRECT_URL` with production domain
- [ ] Verify `APP_KEY` is set correctly
- [ ] Test all routes return HTTPS URLs
- [ ] Check OAuth callback works with HTTPS

## Troubleshooting

**Issue: Mixed content warnings in browser**
- Ensure `APP_ENV=production` and `APP_URL` uses `https://`
- Clear browser cache and reload

**Issue: OAuth redirect fails**
- Verify `GOOGLE_REDIRECT_URL` in `.env` matches Google Console configuration
- Ensure URL uses HTTPS in production

**Issue: Assets fail to load**
- Check that `APP_URL` is correct in `.env`
- Verify SSL certificate is valid
- Use `secure_asset()` for critical assets

## References

- [Laravel URL Helpers](https://laravel.com/docs/routing#urls)
- [Laravel Security Best Practices](https://laravel.com/docs/security)
- [HTTPS Security](https://owasp.org/www-project-web-security-testing-guide/latest/4-Web_Application_Security_Testing/01-Information_Gathering/01-Conduct_Web_Application_Fingerprinting)
