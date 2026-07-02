# Webhooks Handler for Emailing providers

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![codecov](https://codecov.io/gh/Erwane/whep-client/graph/badge.svg?token=L98IZZFBY2)](https://codecov.io/gh/Erwane/whep-client)
[![CI](https://github.com/Erwane/whep-client/actions/workflows/ci.yml/badge.svg)](https://github.com/Erwane/whep-client/actions)
[![Packagist Downloads](https://img.shields.io/packagist/dt/Erwane/whep-client)](https://packagist.org/packages/Erwane/whep-client)
[![Packagist Version](https://img.shields.io/packagist/v/Erwane/whep-client)](https://packagist.org/packages/Erwane/whep-client)

This is the base project to easily handle webhooks sent by different emailing providers and uniformizing in
a comprehensive object.

## Migrating to v3

With v3, all providers are included into this lib, so, you don't need providers children packages anymore.

## Use composer

```shell
composer require erwane/whep-client
```

**Warning**: For [Mailgun provider](docs/Mailgun.md), additional vendor is required.
```
composer require dflydev/dot-access-data:"^3.0"
```

## Usage

See [Documentations](docs/README.md)
