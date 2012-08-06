Rudolf.php
==========

An experiment with easyRdf (https://github.com/njh/easyrdf) & BBC Programme data.

Gets some details about upcoming sci-fi programmes on the BBC.

    $rudolf = new Rudolf();
    $programmeDetails = $rudolf->main();

    foreach($programmeDetails as $programme){
            print $programme['title'] . "\n";
            print $programme['synopsis'] . "\n";
            print "\n\n";
    }