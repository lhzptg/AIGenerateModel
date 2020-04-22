<?php

/**
 * 生成model类，使用方法：
 * php createModel.php tableName
 * tableName表示数据库表名，不输人表名，默认生成所有表model
 */

$configs = [
    'user' => 'xxxxx',
    'host' => 'xxxxxxx',
    'port' => '3306',
    'dbname' => 'db',
    'password' => 'xxxxxxxx',
    'table' => empty($argv[1]) ? '' : trim($argv[1]),
];

//数据库操作
$pdo = new PDO(
    "mysql:host={$configs['host']};port={$configs['port']};dbname={$configs['dbname']};charset=UTF8",
    $configs['user'],
    $configs['password'],
    [
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_NUM,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
    ]
);
$pdo->query("set names utf8mb4");

$dir = __DIR__ . "/{$configs['dbname']}_mysql_model/";
is_dir($dir) ? chmod($dir, 0777) : mkdir($dir, 0777, true);

$tables = [];
if ($configs['table']) {
    $tables[] = $configs['table'];
} else {
    $tables = array_column((array)$pdo->query("show tables")->fetchAll(), '0');
}
foreach ((array)$tables as $key => $table) {
    if (!$pdo->query("show tables like '{$table}'")) {
        continue;
    }
    $pri_key = '';
    $field = $pdo->query("show full fields from `{$table}`;")->fetchAll(PDO::FETCH_ASSOC);
    $fid = '';
    foreach ((array)$field as $fie) {
        $fid .= "'{$fie['Field']}',";
        if ($fie['Key'] == 'PRI') {
            $pri_key = $fie['Field'];
        }
    }
    $fields = array_filter(array_unique(array_column($field, 'Field')));
    $class_name = preg_replace_callback('/_(\w)/', function ($matches) {
        return ucwords($matches[1]);
    }, $table);
    $class_table = substr($table, strrpos($table, 'xb_') + 3);
    $class_name = ucwords($class_name) . 'Model';
    $dates = date('Y-m-d H:i:s');
    $str = <<<EOT
<?php

namespace App\Models;

/**
* @Auth  AI技术生成Model
* @Desc {$class_name}类
* @Date {$dates}
*/
class {$class_name} extends Model
{

    protected  \$table = '{$class_table}';
    
    protected  \$fields = [
                     {$fid}
                ];     

    public function getXxxByXxx()
    {
        // 示例....
    }
    
}
EOT;
    $file_name = "{$dir}{$class_name}.php";
    echo (file_put_contents($file_name, $str) ? "生成类文件成功：{$file_name}" : "生成类文件失败：{$file_name}") . "\n";
}
