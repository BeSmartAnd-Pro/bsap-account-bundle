# BSAP Account Bundle

Bundle provides authentication and invoice client integration for BSAP Accounting.

## Installation

```bash
composer require besmartand-pro/bsap-account-bundle
```

## Configuration

Default endpoints point to the production BSAP Accounting instance:

- `https://ksiegowosc.besmartand.pro/api/login_check`
- `https://ksiegowosc.besmartand.pro/api/graphql`

Use `alternativeHost` only when you want to connect to a different environment, for example local or development.

```yaml
bsap_account:
  username: '%env(resolve:BESMARTANDPRO_KSIEGOWOSC_USERNAME)%'
  password: '%env(resolve:BESMARTANDPRO_KSIEGOWOSC_PASSWORD)%'
  alternativeHost: '%env(resolve:BESMARTANDPRO_KSIEGOWOSC_HOST)%'
```

If `alternativeHost` is set to `https://ksiegowosc.dev.besmartand.pro`, the bundle will use:

- `https://ksiegowosc.dev.besmartand.pro/api/login_check`
- `https://ksiegowosc.dev.besmartand.pro/api/graphql`

If you do not need an override, omit `alternativeHost`.
