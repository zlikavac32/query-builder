<?php

declare(strict_types=1);

namespace Zlikavac32\QueryBuilder;

use Zlikavac32\Enum\Enum;

/**
 * @method static StatementSection MODIFIERS
 * @method static StatementSection COLUMNS
 * @method static StatementSection FIRST_INTO
 * @method static StatementSection TABLES
 * @method static StatementSection PARTITION
 * @method static StatementSection WHERE
 * @method static StatementSection GROUP_BY
 * @method static StatementSection HAVING
 * @method static StatementSection ORDER_BY
 * @method static StatementSection LIMIT
 * @method static StatementSection PROCEDURE
 * @method static StatementSection SECOND_INTO
 * @method static StatementSection FLAGS
 */
abstract class StatementSection extends Enum
{

}
