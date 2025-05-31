<?php

namespace App\Providers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        DB::listen(function (QueryExecuted $query) {
            $time = $query->time;
            $sql = \Illuminate\Support\Str::replaceArray('?', $query->bindings, $query->sql);
            foreach ($query->bindings as $key => $value) {
                // Use regex to replace the named bindings with actual values
                $value = is_numeric($value) ? $value : "'" . addslashes($value) . "'";
                $sql = preg_replace("/:$key\b/", $value, $sql);
            }
            $sql = self::prettyPrintSQL($sql);
            $now = date('Y-m-d H:i:s');
            $logStr = "- SQL: $now \n$sql\n----------------------------------------------------------------";
            $placeholder = '';
        });
    }

    public static function prettyPrintSQL($sql)
    {
        // Keywords to capitalize
        $keywords = [
            'SELECT',
            'FROM',
            'WHERE',
            'LIMIT',
            'ORDER BY',
            'GROUP BY',
            'INNER JOIN',
            'LEFT JOIN',
            'RIGHT JOIN',
            'JOIN',
            'ON',
            'AND',
            'OR',
            'INSERT INTO',
            'VALUES',
            'UPDATE',
            'SET',
            'DELETE',
            'AS',
            'IN',
            'IS',
            'NOT',
            'NULL',
            'UNION',
            'UNION ALL',
            'HAVING',
        ];

        // Capitalize keywords
        foreach ($keywords as $keyword) {
            $pattern = '/\b' . preg_quote($keyword, '/') . '\b/i';
            $sql = preg_replace_callback($pattern, function ($matches) {
                return strtoupper($matches[0]);
            }, $sql);
        }

        // Adding newlines before each major clause
        $sql = preg_replace('/\b(SELECT|FROM|WHERE|LIMIT|ORDER BY|GROUP BY|INNER JOIN|LEFT JOIN|RIGHT JOIN|JOIN|SET|HAVING|UNION|UNION ALL)\b/', "\n$0", $sql);

        // Indentation for SELECT columns
        $sql = preg_replace('/\bSELECT\b/', "SELECT\n    ", $sql);

        // Indentation for other clauses
        $sql = preg_replace('/\b(FROM|WHERE|GROUP BY|ORDER BY|HAVING|LIMIT)\b/', "\n$0", $sql);

        // Indentation for AND, OR, and ON
        $sql = preg_replace('/\b(AND|OR|ON)\b/', "\n    $0", $sql);

        // Ensure consistent spacing and formatting for subqueries
        $sql = preg_replace('/\(\s+/', '(', $sql); // Remove extra space after opening parenthesis
        $sql = preg_replace('/\s+\)/', ')', $sql); // Remove extra space before closing parenthesis

        // Cleanup: Remove unnecessary multiple newlines
        $sql = preg_replace("/\n{2,}/", "\n", $sql);

        // Add semicolon at the end of the query if not already present
        $sql = trim($sql);
        if (substr($sql, -1) !== ';') {
            $sql .= ';';
        }

        return $sql;
    }
}
