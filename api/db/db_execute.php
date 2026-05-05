<?php
function dbFetch($db, $cols, $table, $condition, $execute)
{
    $sql = "SELECT $cols FROM $table $condition";
    $stmt = $db->prepare($sql);
    $stmt->execute($execute);

    return $stmt->fetch();
}//Fetch row.

function dbFetchAll($db, $cols, $table, $condition, $execute)
{
    $sql = "SELECT $cols FROM $table $condition";
    $stmt = $db->prepare($sql);
    $stmt->execute($execute);

    return $stmt->fetchAll();
}//Fetch all rows.

function dbSqlExecute($db, $sql, $execute)
{
    $stmt = $db->prepare($sql);
    $stmt->execute($execute);
}//Execute whatever.

function dbInsertInto($db, $table, $cols, $execute)
{

    $clean = trim($cols, "() ");
    $columns = explode(',', $clean);

    $placeholders = array_fill(0, count($columns), '?');

    $values = "(" . implode(', ', $placeholders) . ")";

    $sql = "INSERT INTO $table $cols VALUES $values";
    dbSqlExecute($db, $sql, $execute);
}

function dbInsertOnDup()
{

}

function dbDelete($db, $table, $where, $execute)
{
    $sql = "DELETE FROM $table WHERE $where";
    dbSqlExecute($db, $sql, $execute);
}//dbDelete