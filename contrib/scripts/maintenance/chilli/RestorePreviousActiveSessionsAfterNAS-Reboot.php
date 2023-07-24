<?php

 # GET ALL DEVICES CONNECTED TO HOTSPOT WITHOUT LOGIN
 $result = str_replace("\n", "|", trim(shell_exec("sudo chilli_query list | grep 'dnat' | awk '{print $1}'")));


 $macs_connected = explode("|",$result);

 echo "<h1>Connected devices without login</h1>";
 echo "<pre>";
 print_r($macs_connected);
 echo "</pre>";

 # GET ALL UNCLOSED SESSIONS FROM DATABASE THAT MATCH THE CURRENTLY CONNECTED DEVICES WITHOUT LOGIN
 $link = mysqli_connect("127.0.0.1", "cheserem", "  m", "radius");
 // Below query returns the client device mac_address and the username for a session that had an acctterminatecause of "NAS-Reboot"

 #$query = "SELECT any_value(radcheck.value) as UserPassword,radacct.CallingStationId,any_value(radacct.username) as UserName, any_value(radacct.framedipaddress) as FramedIpAddress,any_value(radacct.acctstoptime) as AcctStopTime,any_value(acctterminatecause) as acctterminatecause FROM radius.radacct INNER JOIN radcheck ON radcheck.username = radacct.username WHERE radacct.UserName != 'admin' and radcheck.attribute = 'Cleartext-Password' and month(AcctStopTime) = month(now()) and acctterminatecause = 'NAS-Reboot' and radacct.nas_session_restored is null and radacct.CallingStationId in ('".implode("','",$macs_connected)."')  Group BY radacct.CallingStationId";


$query = "SELECT DISTINCT(radacct.username) as UserName,radcheck.value as UserPassword, radacct.acctterminatecause, radacct.acctstoptime as AcctStopTime, radacct.framedipaddress as FramedIpAddress, radacct.callingstationid as CallingStationId from radacct
inner join radcheck on radacct.username = radcheck.username
where radacct.CallingStationId in ('".implode("','",$macs_connected)."')
and radacct.username != 'admin'
and radcheck.attribute = 'Cleartext-Password'
and length(FramedIpAddress) > 1
and length(CallingStationId) > 1
and radacct.username not in (select username from radacct where radacct.acctterminatecause in ('Session-Timeout','User-Request','Admin-Reset'))
ORDER BY `UserName` DESC ";

 if ($result = $link->query($query))
 {
    $loggedin = array();
    // var_dump($result->fetch_assoc());

    while ($device= $result->fetch_assoc()) {
      // Restore Sessions with Lost-Carrier
        //shell_exec("sudo chilli_query authorize mac ".$device['CallingStationId']." username ".$device['UserName']);
        shell_exec("sudo chilli_query -s /var/run/chilli.wlo1.sock login mac ".$device['CallingStationId']." username ".$device['UserName']. " password ".$device['UserPassword'] );
        array_push($loggedin,$device['CallingStationId']);
        # // TODO: Once a NAS-Reboot session has been restored we need to update a column in the radacct table with a flag that shows the session has been restored.
        // The latter will help resolve the issue of the session being restored even if a user has intentionally logged out - User-Request
    }
    $result->free();
    echo "<h1>Devices we auto-logged in</h1>";
    var_dump($loggedin);
 }
 $link->close();
?>

/*
*


SELECT DISTINCT(radacct.username) as UserName,radcheck.value as UserPassword, radacct.acctterminatecause, radacct.acctstoptime as AcctStopTime, radacct.framedipaddress as FramedIpAddress, radacct.callingstationid as CallingStationId from radacct
inner join radcheck on radacct.username = radcheck.username
#where radacct.CallingStationId in ('CA-0E-B4-A6-FB-B9','FC-02-96-C0-78-D8','D0-37-45-E8-94-51')
where radacct.CallingStationId in ('".implode("','",$macs_connected)."')
and radacct.username != 'admin'
and radcheck.attribute = 'Cleartext-Password'
and length(FramedIpAddress) > 1
and length(CallingStationId) > 1
and radacct.username not in (select username from radacct where radacct.acctterminatecause in ('Session-Timeout','User-Request','Admin-Reset'))
#and month(AcctStopTime) <= month(now())
ORDER BY `UserName` DESC

*/
