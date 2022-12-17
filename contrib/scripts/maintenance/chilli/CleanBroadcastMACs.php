<?php

# perform a fix for chilli where when we run # chilli_query list
# we are met with the mac address of 00-00-00-00-00-00
# the above later affects the acct.nasipaddress by filling it up with the latter mac.



// $needle = array(
//     0=>'80', 1=>'00'
//     );
//     print_r($needle);
//
//     if (in_array('00', $needle)) {
//         echo "'ph' was found\n";
//         $needle = array_diff($needle, ['00']);
//
//         // print_r($values);
//         // unset($needle);
//         // $needle = array();
//         print_r($needle);
//     }



/*
if (in_array('00-00-00-00-00-00', $macs_connected)) {
  shell_exec("sudo chilli_query dhcp-release 00-00-00-00-00-00");
  print_r($macs_connected);
  $macs_connected = array_diff($macs_connected, ['00-00-00-00-00-00']);
  echo "00-00-00-00-00-00 was found and cleared.\n";
}

*/
# fix the Error of Connection refused - when chilli daemon is not running

//// TODO: Implement Usage of a try-catch-block to handle the returned errors.

$result = str_replace("\n", "|", trim(shell_exec("sudo chilli_query list | grep 'none' | awk '{print $1}'")));

$macs_connected = explode("|",$result);
 echo "Before removing\n";
    print_r($macs_connected);
    if (!empty($macs_connected) )
    {
      if (in_array(null, $macs_connected, true) || in_array('', $macs_connected, true)) {
    // There are null (or empty) values.
    echo "Exit: No Rogue MACs reported by chilli_query list\n";
    }
    else {
    // code...
    $count = 0;
    foreach($macs_connected as $key => $value) {
      // $value = "00-00-00-00-00-00";
      // print_r($value);
      // echo gettype($value);
      // echo '".implode("','",$macs_connected)."';
      // echo "\n";
      // echo $macs_connected[$key];

      //'".implode("','",$macs_connected)."'

      shell_exec("sudo chilli_query -s /var/run/chilli.wlo1.sock dhcp-release ".$value );
      // shell_exec("sudo chilli_query dhcp-release ".$value );

    }
    echo "Success: Slaughtered Noisy Rogue MAC[s] returned by chilli_query list\n";
  }


    }


// $result = str_replace("\n", "|", trim(shell_exec("sudo chilli_query list | grep 'none' | awk '{print $1}'")));
//
// $macs_connected = explode("|",$result);
// echo "After removing";
// print_r($macs_connected);
