# BarbeQueue

[![Tests](https://github.com/benedekb97/barbequeue/actions/workflows/ci.yml/badge.svg)](https://github.com/benedekb97/barbequeue/actions/workflows/ci.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

BarbeQueue is an open-source deployment queue manager for teams running multiple services.

It helps developers and DevOps teams answer the most important question during busy release cycles:

> **Can I deploy yet?**

Instead of deployment chaos, BarbeQueue brings order by coordinating deployments through Slack-based queues.

---

## What BarbeQueue Does

When multiple developers are trying to deploy at the same time, teams often lose time to:

- waiting on each other
- unclear ownership of the next deploy slot
- accidental overlaps between services
- constant “is it my turn?” messages

BarbeQueue solves this by providing structured deployment queues directly inside Slack.

---

## Features

- Join deployment queues directly from Slack
- Get notified automatically when it's your turn
- Leave queues manually or be removed automatically when finished
- Interactive Slack buttons and modals for smooth UX
- Supports multiple independent queues (per service/team/etc.)
- REST API exposed for integrations and automation
- Slack authentication required for all actions

---

## Slack Commands

BarbeQueue provides a simple command-based workflow:

```bash
/bbq join {queue} {?time}
/bbq leave {queue}
/bbq list {queue}
/bbq help
````

### Examples

Join a deployment queue:

```bash
/bbq join backend
```

Join with an estimated deployment time:

```bash
/bbq join backend 15m
```

List current queue members:

```bash
/bbq list backend
```

Leave when you're finished:

```bash
/bbq leave backend
```

Most actions are also supported through interactive buttons and modals inside Slack.

---

## 🛠 Tech Stack

BarbeQueue is built with:

* **Backend:** Symfony (FrankenPHP)
* **Database:** PostgreSQL
* **Queue/State:** Redis
* **Integration:** Slack API
* **Deployment:** Docker / Docker Compose

---

## Getting Started

### Requirements

* Docker
* Docker Compose
* A Slack App configured with the required credentials

---

## Environment Variables

BarbeQueue requires the following environment variables:

| Variable               | Description                                   |
| ---------------------- | --------------------------------------------- |
| `SLACK_CLIENT_ID`      | Slack OAuth Client ID                         |
| `SLACK_CLIENT_SECRET`  | Slack OAuth Client Secret                     |
| `SLACK_SIGNING_SECRET` | Slack signing secret for request verification |
| `POSTGRES_PASSWORD`    | Password for the Postgres container           |

Example:

```env
SLACK_CLIENT_ID=xxx
SLACK_CLIENT_SECRET=xxx
SLACK_SIGNING_SECRET=xxx
POSTGRES_PASSWORD=supersecurepassword
```

---

## Running with Docker

### 1. Build the image

```bash
docker build -t barbequeue .
```

### 2. Set environment variables

Create a `.env` file (see above).

### 3. Start the stack

```bash
docker compose up
```

Once running, BarbeQueue will be ready to connect to your Slack workspace.

---

## REST API

BarbeQueue exposes a REST API for integrations and automation.

All endpoints require authentication via Slack sign-in.

API documentation available at `/doc/openapi.json`

---

## Contributing

Contributions are welcome!

If you’d like to help:

1. Fork the repository
2. Create a feature branch
3. Submit a pull request

Ideas, issues, and feedback are always appreciated.

---

## License

BarbeQueue is released under the **MIT License**.

---

## Why "BarbeQueue"?

Because deployments are like barbecues:

Everyone wants their turn, but nobody wants the grill to catch fire.
