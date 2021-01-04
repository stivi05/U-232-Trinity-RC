<?php
/**
 * -------   U-232 Codename Trinity   ----------*
 * ---------------------------------------------*
 * --------  @authors U-232 Team  --------------*
 * ---------------------------------------------*
 * -----  @site https://u-232.duckdns.org/  ----*
 * ---------------------------------------------*
 * -----  @copyright 2020 U-232 Team  ----------*
 * ---------------------------------------------*
 * ------------  @version V6  ------------------*
 */
function docleanup($data)
{
    global $TRINITY20, $queries, $cache, $keys;
    set_time_limit(1200);
    ignore_user_abort(1);
    //== delete torrents - ????
    $days = 30;
    $dt = (TIME_NOW - ($days * 86400));
    sql_query("UPDATE torrents SET flags='1' WHERE mtime < $dt AND seeders='0' AND leechers='0'") or sqlerr(__FILE__, __LINE__);
    $res = sql_query("SELECT id, name FROM torrents WHERE mtime < $dt AND seeders='0' AND leechers='0' AND flags='1'") or sqlerr(__FILE__, __LINE__);
    while ($arr = mysqli_fetch_assoc($res)) {
        sql_query("DELETE files.*, comments.*, thankyou.*, thanks.*, thumbsup.*, bookmarks.*, coins.*, rating.*, xbt_peers.* FROM xbt_peers
                                 LEFT JOIN files ON files.torrent = xbt_peers.tid
                                 LEFT JOIN comments ON comments.torrent = xbt_peers.tid
                                 LEFT JOIN thankyou ON thankyou.torid = xbt_peers.tid
                                 LEFT JOIN thanks ON thanks.torrentid = xbt_peers.tid
                                 LEFT JOIN bookmarks ON bookmarks.torrentid = xbt_peers.tid
                                 LEFT JOIN coins ON coins.torrentid = xbt_peers.tid
                                 LEFT JOIN rating ON rating.torrent = xbt_peers.tid
                                 LEFT JOIN thumbsup ON thumbsup.torrentid = xbt_peers.tid
                                 WHERE xbt_peers.tid =" . sqlesc($arr['id'])) or sqlerr(__FILE__, __LINE__);
        
        @unlink("{$TRINITY20['torrent_dir']}/{$arr['id']}.torrent");
        write_log("Torrent ".(int)$arr['id']." (".htmlsafechars($arr['name']).") was deleted by system (older than $days days and no seeders)");
    }
    if ($queries > 0) write_log("Delete Old Torrents XBT Clean -------------------- Delete Old XBT Torrents cleanup Complete using $queries queries --------------------");
    if (false !== mysqli_affected_rows($GLOBALS["___mysqli_ston"])) {
        $data['clean_desc'] = mysqli_affected_rows($GLOBALS["___mysqli_ston"]) . " items deleted/updated";
    }
    if ($data['clean_log']) {
        cleanup_log($data);
    }
}
?>
