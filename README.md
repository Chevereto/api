# Chevereto API

[![Build](https://img.shields.io/github/workflow/status/chevereto/api/CI/master?style=flat-square)](https://github.com/chevereto/api/actions)
[![codecov](https://img.shields.io/codecov/c/github/chevereto/api?style=flat-square)](https://codecov.io/gh/chevereto/api)
[![CodeFactor](https://img.shields.io/codefactor/grade/github/chevereto/api?label=code%20grade&style=flat-square)](https://www.codefactor.io/repository/github/chevereto/api)
[![Codacy Badge](https://img.shields.io/codacy/grade/9bc0696e742b438cabb258f9240cac66?style=flat-square)](https://www.codacy.com/gh/Chevereto/api)
[![Maintainability](https://img.shields.io/codeclimate/maintainability/Chevereto/api?style=flat-square)](https://codeclimate.com/github/Chevereto/api)
[![Tech Debt](https://img.shields.io/codeclimate/tech-debt/Chevereto/api?style=flat-square)](https://codeclimate.com/github/Chevereto/api)
[![MIT License](https://img.shields.io/github/license/chevereto/api?style=flat-square)](LICENSE)

> 🔔 [Subscribe](https://newsletter.chevereto.com/subscription?f=PmL892XuTdfErVq763PCycJQrvZ8PYc9JbsVUttqiPV1zXt6DDtf7lhepEStqE8LhGs8922ZYmGT7CYjMH5uSx23pL6Q) to don't miss any update regarding Chevereto.

![Chevereto](LOGO.svg)

This is the Chevereto API, a server side application providing the public user endpoints that carries instructions to the system. This API is the core element in the provision of the Chevereto V4 architecture, _everything_ is designed around this.

## Technical Overview

Chevereto API is a [Chevere](https://chevere.org/) application written in the [PHP](https://www.php.net/) programming language. It uses [Swoole](https://www.swoole.co.uk/) as an application runner to serve most of the application services.

Chevereto API handles stored application states that are intended to be persistent, without having to bootstrap the whole application on every request. This enables Chevereto API to offer way higher I/O throughput than conventional PHP software. Also, it is built to be heavily extended with plugins altering the default application [Workflows](https://chevere.org/components/Workflow.html).

Chevereto API is headless software and is completely decoupled from the user interface. It exposes a self-described REST API, but also other endpoints to command the application from _anywhere_.

## Contribution

This project is under alpha development and is not recommended to use it for production yet. Developers (and those wanting to become) are welcome! Join our [Discord](https://chv.to/discord) channel for more into this.

## License

Copyright [Rodolfo Berrios A.](https://rodolfoberrios.com/) Chevereto API is licensed under the [MIT license](LICENSE).
