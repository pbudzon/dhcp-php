# dhcp
DHCP implementation in PHP

[![Build Status](https://travis-ci.org/pbudzon/dhcp-php.svg?branch=master)](https://travis-ci.org/pbudzon/dhcp-php)

This is work in progress


PostgreSQL tables:

```
CREATE TABLE dhcp_config
(
  id serial NOT NULL,
  broadcast inet,
  dns inet[],
  lease_time integer,
  mask inet,
  router inet,
  server_ip inet,
  CONSTRAINT dhcp_config_id_pk PRIMARY KEY (id),
  CONSTRAINT dhcp_config_server_ip_pk UNIQUE (server_ip)
)
WITH (
  OIDS=FALSE
);
```

```
CREATE TABLE dhcp_leases
(
  id serial NOT NULL,
  ip inet,
  mac macaddr,
  reason text,
  assigned_on timestamp without time zone,
  expires_on timestamp without time zone,
  CONSTRAINT dhcp_leases_id_pk PRIMARY KEY (id)
)
WITH (
  OIDS=FALSE
);

```

```

CREATE TABLE dhcp_static
(
  id serial NOT NULL,
  ip inet,
  dns inet[],
  lease_time integer,
  mac macaddr,
  router inet,
  CONSTRAINT dhcp_static_id_pk PRIMARY KEY (id),
  CONSTRAINT dhcp_static_ip_pk UNIQUE (ip)
)
WITH (
  OIDS=FALSE
);

```