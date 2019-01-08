#!/usr/bin/perl

use strict;
use warnings;
use Gearman::Client;
use IO::Handle;

my $client = Gearman::Client->new;
$client->job_servers('127.0.0.1');

open( COM, " <", "/dev/ttyACM0") || die "Cannot read serial port : $!\n";

   #Read incoming data.
    while($_ = <COM>)
    {
        print "beep";
       print $_;
#if ($input =="\n") {print "meep";}
# running a single task
#my $result_ref = $client->do_task("call_agent", "meepity");

    }
