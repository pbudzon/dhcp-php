# dhcp
DHCP implementation in PHP

[![Build Status](https://travis-ci.org/pbudzon/dhcp-php.svg?branch=master)](https://travis-ci.org/pbudzon/dhcp-php)

`src/DHCP/` contains an implementation of DHCP protocol in PHP. It can be used to translate any DHCP packet from the 
network into PHP objects and vice versa.

`src/DHCPServer/` is a simple DHCP Server using the DHCP implementation, using PostgreSQL as a backend for storing
lease information. It also supports assigning static ip addresses to clients and was tested with default DHCP Clients
on Windows 7, MacOS Sierra and Red Hat Linux 7 (dhclient).

## Why?

Simply to show it can be done. This code is not intended for production use, but as a learning exercise. It may be
 an easy way for PHP developers to learn more about DHCP protocol.

## Running DHCPServer

To start the server, run `php src/DHCPServer/server.php serve x.x.x.x/y password_to_postgresql`
Replace `x.x.x.x/y` with an IP and mask for the server (for example, 10.0.0.0/25) and `password_to_postgres` with
a password to PostgreSQL. 

To change database IP address, database name and user, go to `src/DHCPServer/Postgresql.php` line 12.

### PostgreSQL tables for DHCPServer

Two PostreSQL tables are required and have to be created before the server can run:

`dhcp_leases` holds information about leases given to clients and their expiry dates:

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

`dhcp_static` holds a list of static IP addresses for each client based on its MAC address:

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