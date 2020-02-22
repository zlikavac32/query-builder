# Query Builder

The SQL query builder library that does not build an SQL query from scratch, but rather modifies an existing query. 

## Table of contents

1. [Introduction](#introduction)
1. [How it works](#how-it-works)
1. [Installation](#installation)
1. [Configuration](#configuration)
    1. [Preloading](#preloading)
1. [Usage](#usage)
    1. [Subquery inlining](#subquery-inlining)
1. [Limitations](#limitations)

## Introduction

How to achieve harmony between the PHP and the database has been a subject to many blog posts. While some propose and use various ORM-s like Doctrine and Eloquent, other stick to raw SQL queries. ORM-s come with some sort of a query builder that is something very useful. For example, when providing filtering to an admin page, it's useful to programmatically modify the query to reflect requested filters.

If we think about a query itself, there are really rare occasions (I myself did not encounter one such) where we need to build a query from scratch. We usually have a base query that does something and then we extend it. In the filtering example above, we could have `SELECT t.id, t.name FROM tickets t WHERE t.date >= '2020-01-01` as a base query for this years tickets and then a filter for `state` that modifies initial query into `SELECT t.id, t.name FROM tickets t WHERE t.date > '2020-01-01' AND t.state = 'SOLD'`.

That is the main premise behind this query builder. To reuse existing queries instead of manually constructing a full query through the query builder.

## How it works?

Every `SELECT` statement is decomposed into sections like `columns` or `where` and then that statement can be modified through the exposed query builder API. To make that possible, the query builder parses statements and tracks all of the parameter placeholders and parameter values themselves and when a certain section is modified, parameters are merged/removed as well.

That means that parameters must be known at the time when that section is being added to the query builder, whether it's the initial query or just some new section. That being said, this query builder is not intended for reusable prepared statements (**yet!**).

The parser used is a [small SQL parser](https://github.com/zlikavac32/sql-query-parser) written in C and exposed through the FFI (more parser implementations may come in the future).

## Installation

The recommended installation is through Composer.

```shell script
composer require zlikavac32/query-builder
```
## Configuration

To use the query builder, create a query builder environment.

```php
use Zlikavac32\QueryBuilder\FFISqlParser;
use Zlikavac32\QueryBuilder\NonPreloadedFFIGateway;
use Zlikavac32\QueryBuilder\ParserBackedQueryEnvironment;

$parser = NonPreloadedFFIGateway::createDefault();
$parser = new FFISqlParser($parser);
$queryBuilder = new ParserBackedQueryEnvironment($parser);
```

### Preloading

Instead of importing the FFI definition when needed, the FFI definition can be preloaded. There is no pretty or automatic way to do it. In your preload script you should add

```php
FFI::load('{{path}}');
```

where `{{path}}` represents a path to the `tsqlp.h` header file in this repository, for example `__DIR__ . '/vendor/zlikavac32/query-builder/tsqlp.h`. Other (perhaps necessary) option is to use `ffi.preload` with the path to the `tsqlp.h`. For more info consult the [FFI configuration](https://www.php.net/manual/en/ffi.configuration.php).

Other change is that you can no longer use the `\Zlikavac32\QueryBuilder\NonPreloadedFFIGateway` class and you should use the `\Zlikavac32\QueryBuilder\PreloadedFFIGateway` class.

## Usage

Use the constructed environment to create a query builder from an `SQL` string or a `\Zlikavac32\QueryBuilder\Query` instance.

```php
$qb = $queryBuilder->queryBuilderFromString(
    'SELECT * FROM user WHERE active = ?', 
    1
);

$qb->andWhere('registered_at > ?', '2020-01-01');

$query = $qb->build();

var_dump($query->sql(), $query->parameters());
// SELECT * FROM user WHERE (active = ?) AND (registered_at > ?)
// [1, '2020-01-01']
```

Check `\Zlikavac32\QueryBuilder\QueryBuilder` for more info regarding available query builder methods.

### Subquery inlining

Since this library parses queries, it can inject subqueries into the parameter placeholders.

```php
use Zlikavac32\QueryBuilder\Query;

$userOfTheLatestTransactionQuery = Query::create(
    'SELECT user_id FROM transaction ORDER BY created_at DESC LIMIT 1'
);
$qb = $queryBuilder->queryBuilderFromString(
    'SELECT * FROM user WHERE id = ?', 
    $userOfTheLatestTransactionQuery
);

$query = $qb->build();

var_dump($query->sql(), $query->parameters());
// SELECT * FROM user WHERE id = (SELECT user_id FROM transaction ORDER BY date DESC LIMIT 1)
// []
```

## Limitations

Used parser could (and probably will) parse certain SQL queries as valid only for them to be rejected from the database. This is by design to keep the parsing simple since that parser is not intended to be used as a linter or whatever. It doesn't even produce an AST and the resulting query will immediately be executed so the logic error will be known. The query builder itself will not produce an invalid query from a valid query. This is not really a limitation, but it's useful to mention.
