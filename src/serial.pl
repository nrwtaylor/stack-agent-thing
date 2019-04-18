#!/usr/bin/perl

use strict;
use warnings;
use Gearman::Client;
use IO::Handle;

my $client = Gearman::Client->new;
$client->job_servers('127.0.0.1');


my $com;

# https://www.perlmonks.org/?node_id=947392
open( $com, "+<","/dev/ttyUSB0" ) || die "Cannot open serial port : $!
+\n";
while( 1 )
{   my $in = <$com>;           # input

    # do stuff here ( You should be able to do both input and output within the loop. 

    print $com "...";          # output

    if ( $in eq "quit" ) { last; }
}


#$arr{'to'} = 'to_test';
#$arr{'from'} = 'from_test';
#$arr{'subject'} = 'subject_test';

    my %arr = (
        'to' => 'hang gliding',
        'from' => 'diving',
        'subject' => 'bus surfing'
    );

my $result_ref = $client->do_task("call_agent", %arr);


my $input;

my $port = "/dev/ttyUSB0";


open( COM, "+<", $port) || die "Cannot read serial port : $!\n";

   #Read incoming data.
    while($input = <COM>)
    {
my $substr = '+CMTI: "SM",';
if (index($input, $substr) != -1) {
    print "$_ contains $substr\n";
} 

        print "beep";
       print $input;
if ($input =="\n") {print "meep";}
# running a single task
my $result_ref = $client->do_task("call_agent", "meepity");
}

#   portSMS.print(F("AT+CMGR="));
#   portSMS.print(number);
#   portSMS.write(0x0D);  // Carriage Return in Hex
#    if (isAgent()) {
#    }        
#  }

#  if (strstr(agent,"+SMS FULL")) {
#    strcpy(response, "SMS Full.");
#    strcpy(instruction, "+");
#    doForget();
#  }

#    }
