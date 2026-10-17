<?php

declare(strict_types=1);

namespace Gll\Hub\Contracts\Package;

/** The kinds of setting the admin knows how to draw a field for. */
enum SettingType: string
{
    case String = 'string';
    case Text = 'text';
    case Integer = 'integer';
    case Boolean = 'boolean';
    case Url = 'url';
    case Enum = 'enum';
    case StringList = 'string_list';
}
