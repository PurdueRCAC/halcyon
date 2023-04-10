# CILogon Authenticator

This is a basic authenticator for [CILogon](https://cilogon.org)

CILogon facilitates federated authentication for CyberInfrastructure (CI). For more information, see http://www.cilogon.org/oidc . Note that CILogon is used primarily by NSF-funded projects. All client registrations are vetted and approved manually.

Basic Authentication service tier (free) users should register your client at https://cilogon.org/oauth2/register and wait for notice of approval. Please register your callback URL as `{your site}/callback` (ex: https://research.university.edu/callback).

This authenticator requires the CILogon OAuth 2.0 Client Provider.

```
composer require cilogon/oauth2-cilogon
```
