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