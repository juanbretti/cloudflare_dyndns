# Cloudflare Simple DynDNS

A very simple way to update DNS records using Cloudflare and PHP.

After reading the very comprehensive scripts from:

- https://github.com/peppelg/Cloudflare-DDNS.php
- https://github.com/nickian/Cloudflare-Dynamic-DNS

And more about the Cloudflare API:
- https://developers.cloudflare.com/api/

I decided to post my simple solution.

# What it does?

This PHP script is a **dynamic DNS** (DynDNS) updater that uses the **Cloudflare API** to update the DNS record for a specified hostname with the current public IP address of the server where the script is running. Here's a step-by-step explanation:

1. **Fetching Parameters:**
   - Retrieves parameters from the query string of the URL (assumed to be provided via a GET request): `ip`, `hostname`, `email`, and `key`.

2. **Comparing Current IP:**
   - Compares the current IP address obtained from the DNS record with the provided IP address.

3. **Updating DNS Record (if IP differs):**
    - If the IP addresses do not match, it sends a PUT request to update the DNS record with the new IP address.

4. **Sending Email Report:**
    - Sends an email notification with the result of the operation (success or failure) to the specified email address.

Note: The script uses the `mail` PHP function to send emails, so make sure that the mail configuration on the server is set up correctly for this to work. Also, the script assumes that the necessary `cURL` extension is enabled in PHP.

# How it works

1. Edit the mail parameters on the first lines at `cloudflare_dyndns.php`:
   - `$mail_to`: Your personal email, e.g., `Your Name <you@domain.com>`
   - `$mail_from`: A random reply address. This address has no use, because the mail are being send by `mail` from PHP. For example, you can use `Cloudflare DynDNS <noreply@domain.com>`.

3. Host the file `cloudflare_dyndns.php` in any of your webservers.

4. Open the following URL to update your DNS records:
```
https://www.domain.com/dyndns/cloudflare_dyndns.php?hostname=__HOSTNAME__&ip=__MYIP__&email=__USERNAME__&key=__PASSWORD__
```

Where:
- `__HOSTNAME__`: Your hostname, e.g.,  `home.domain.com`
- `__MYIP__`: Your local IP, e.g., `20.1.2.3`
- `__USERNAME__`: Your registered email at Cloudflare, e.g., `you@domain.com`
- `__PASSWORD__`: Your _API Key_, e.g., `100bf38cc8393103870917dd535e0628`

To get your _API Key_, go to:

https://dash.cloudflare.com/profile/api-tokens >> API Keys >> Global API Key

Tries to work the same way as (_the former_) **Google Domains**: https://support.google.com/domains/answer/6147083?hl=en#
