#!/usr/bin/perl -w

# readUNO.pl
# v1.1 emgi May2017

use strict;
use Device::SerialPort;
use Time::HiRes qw( usleep);

my $PT = "/dev/ttyUSB0";
my $port = new Device::SerialPort ($PT) || die "Can't open $PT: $!\n";

$port->baudrate(9600);
$port->databits(8);
$port->parity("none");
$port->stopbits(1);
$port->handshake("none");
$port->write_settings;

my $count;
my $outstring='Q';
my $reply='';
my $blockingflags = 0;
my $inbytes = 0;
my $outbytes = 0;
my $errflags = 0;
my $sent=0;

do {
$outbytes = $port->write($outstring); $sent++;
($blockingflags, $inbytes, $outbytes, $errflags) = $port->status;
#print "in: $inbytes, out: $outbytes\n";
($count,$reply) = $port->read($inbytes);
usleep (60000);
} until ($count);

my $total=$sent*60;
print "Time: $total ms\n";

my $dummy;
my $A0;
my $A1;

my ($line1,$line2)=split /\n/, $reply;
($dummy, $A0)=split / /,$line1;
($dummy, $A1)=split / /,$line2;

print "A0: $A0\n";
print "A1: $A1\n";
