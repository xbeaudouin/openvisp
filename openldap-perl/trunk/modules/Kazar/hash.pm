# $Id: hash.pm,v 1.1 2006-06-26 22:28:58 kiwi Exp $
# 
# Hashing function for directories
package Kazar::hash;

use POSIX;
use strict;

sub hashed
{
        my ($name) = @_;
	# Get only the 4th caracters
	$name = substr($name,0,4);

	# Remove any "dangerous" chars
	$name =~ s/ //g;
	$name =~ s/\.//g;
	$name =~ s/-//g;

        # Split the returned string into a full path, including
        # the specified filename.
        return join ('/', ($name =~ /./g), $_[0]);
}

1;
