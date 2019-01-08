# Perl Polling Test

# Title: Perl to chipKIT Serial Poll Test
# Author: James M. Lynes, Jr.
# Version: 1.0
# Creation Date: June 10, 2012
# Modification Date: June 10, 2012

use strict;
use warnings;

# Initialize the serial port - creates the serial port object $Arduino

use Device::SerialPort::Arduino;

  my $Arduino = Device::SerialPort::Arduino->new(
    port     => '/dev/ttyUSB0',
    baudrate => 9600,
    databits => 8,
    parity   => 'none',
  );

while(1) {
  $Arduino->communicate('P');                     
  # Send a poll
  print ($Arduino->receive(), "\n");              
  # Print poll response
  sleep(1);                                       
  # Delay until next poll
}
