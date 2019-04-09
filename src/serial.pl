#!/usr/bin/perl

use strict;
use warnings;
use Gearman::Client;
use IO::Handle;

my $client = Gearman::Client->new;
$client->job_servers('127.0.0.1');
#my $port = "/dev/ttyUSB0";



# For Linux
use Device::SerialPort;
my $port = Device::SerialPort->new("/dev/ttyUSB0");

# For Windows. You only need one or the other.
# Uncomment these for Windows and comment out above
#use Win32::SerialPort;
#my $port = Win32::SerialPort->new("COM3");

$port->baudrate(115200); # Configure this to match your device
$port->databits(8);
$port->parity("none");
$port->stopbits(1);

#$port->write("\nBegin perl serial listener\n");

while (1) {
#    my $char = $port->lookfor();
    my $char = $port->input;

    if ($char) {
#        print "Received character: $char \n";
        print $char;

my $substr = '+CMTI: "SM",';
if (index($char, $substr) != -1) {


$char =~ s/\Q$substr/AT+CMGR=/ig;
    $port->write($char);
} 



my %arr;
$arr{'to'} = 'to_test';
$arr{'from'} = 'from_test';
$arr{'subject'} = 'subject_test';
#    my %arr = (
#        'to' => 'hang gliding',
#        'from' => 'diving',
#        'subject' => 'bus surfing'
#    );

my $result_ref = $client->do_task("call_agent", %arr);



    }




    $port->lookclear; # needed to prevent blocking
#    sleep (1);
}

# Writing to the serial port
# $port->write("This message going out over serial");








#open( COM, " <", $port) || die "Cannot read serial port : $!\n";

   #Read incoming data.
#    while($_ = <COM>)
#    {
#my $substr = '+CMTI: "SM",';
#if (index($_, $substr) != -1) {
#    print "$_ contains $substr\n";
#} 

#        print "beep";
#       print $_;
#if ($input =="\n") {print "meep";}
# running a single task
#my $result_ref = $client->do_task("call_agent", "meepity");


#    portSMS.print(F("AT+CMGR="));
#    portSMS.print(number);
#    portSMS.write(0x0D);  // Carriage Return in Hex

#    if (isAgent()) {
#    }        
#  }

#  if (strstr(agent,"+SMS FULL")) {
#    strcpy(response, "SMS Full.");
#    strcpy(instruction, "+");
#    doForget();
#  }

#    }
