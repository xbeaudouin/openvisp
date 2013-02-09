#!/usr/bin/perl -w

#use strict;
use File::Find;
use File::Basename;


my $LINE = "";
my %CLASSES = ();
my %FUNCTIONS = ();
my $FILE2PARSE = "";
my $CURCLASS = my $EXTCURCLASS = my $CURFUNCTION = "";
my @IGNFUNCTION = ();
my $current_line = 0;
my $dirname = dirname(__FILE__);
my %FCTUSAGE = ();
my %CLASSUSAGE = ();
my @OBJ_LIST = ();
my $CUROBJ = "";
my $CURMETHOD = "";
my %CLASSREL = ();

open (FILE, $dirname."/graph_call.php_functions.txt");
while (<FILE>){
  chomp;
  push (@IGNFUNCTION, $_);
}
close (FILE);

open (FILE, $dirname."/graph_call.BL.txt");
while (<FILE>){
  chomp;
  push (@IGNFUNCTION, $_);
}
close (FILE);


find ( {wanted=> \&parse_lib, no_chdir => 1}, $ARGV[0]."/lib" );

find ( {wanted=> \&wanted, no_chdir => 1}, $ARGV[0] );

open (FILEOUT, "> functions_graph.dot");

print FILEOUT 'digraph G  {
  graph [fontsize=30 labelloc="t" label="" splines=true overlap=false rankdir = "LR"];
  ratio = auto;
  center=""
  ';

#$FCTUSAGE{$CURFUNCTION}{$FILE2PARSE}{$current_line}
foreach my $discfunction (keys %FCTUSAGE) {
  #print "$discfunction\n";

  foreach my $file (keys %{$FCTUSAGE{$discfunction}}){
    #print "$file\n";
    print FILEOUT " \"$discfunction\" -> \"$discfunction\_$file\";\n";
    print FILEOUT " \"$discfunction\_$file\" [ label = \"$file\" ];\n";

    foreach my $line (keys %{$FCTUSAGE{$discfunction}{$file}}){
      #print "$line\n";
      print FILEOUT " \"$discfunction\_$file\" -> \"$discfunction\_$file\_$line\";\n";
      print FILEOUT " \"$discfunction\_$file\_$line\" [ label = \"$line\" ];\n\n";
    }
  }

}


print FILEOUT '

}
';

close FILEOUT;

print "List of unused functions : \n";

foreach my $CURFUNCTION (keys %FUNCTIONS){

  #print "$CURFUNCTION declared in FUNCTIONS\n";

  if ( exists($FUNCTIONS{$CURFUNCTION}) ){

    #print "$CURFUNCTION :: (".$FUNCTIONS{$CURFUNCTION}{'counter'}.") // ";
    if ( $FUNCTIONS{$CURFUNCTION}{'counter'} == 0 ){
      #print FILEOUT "$function\-is-not-used\n";
      print "function $CURFUNCTION not used (".$FUNCTIONS{$CURFUNCTION}{'counter'}.")\n";
    }

  }
  else{

    print "BIZAR : $CURFUNCTION\n";

  }

}

open (FILEOUT, "> classes_graph.dot");

print FILEOUT 'digraph G  {
  graph [fontsize=30 labelloc="t" label="" splines=true overlap=false rankdir = "LR"];
  ratio = auto;
  center=""

';

#$FCTUSAGE{$CURFUNCTION}{$FILE2PARSE}{$current_line}
foreach my $discclasses (keys %CLASSUSAGE) {
  #print "$discfunction\n";

  print FILEOUT " OVAHTDOCS -> $discclasses;\n";

  foreach my $method (keys %{$CLASSUSAGE{$discclasses}}){

    print FILEOUT " \"$discclasses\" -> \"$discclasses-$method\";\n";
    print FILEOUT " \"$discclasses-$method\" [ label = \"$method\" ];\n";

    foreach my $file (keys %{$CLASSUSAGE{$discclasses}{$method}}) {
      #print "$file\n";
      if ( $file ne "counter"){

        print FILEOUT " \"$discclasses-$method\" -> \"$discclasses-$method\_$file\";\n";
        print FILEOUT " \"$discclasses-$method\_$file\" [ label = \"$file\" ];\n";

        foreach my $line (keys %{$CLASSUSAGE{$discclasses}{$method}{$file}}) {
          #print "$line\n";
          print FILEOUT " \"$discclasses-$method\_$file\" -> \"$discclasses-$method\_$file\_$line\";\n";
          print FILEOUT " \"$discclasses-$method\_$file\_$line\" [ label = \"$line\" ];\n\n";
        }

      }
    }

  }

}


print FILEOUT '

}
';

close (FILEOUT);

sub parse_lib {

  $FILE2PARSE=$_;
  if ( $FILE2PARSE =~ /.*\.php$/ ){

    if ( $FILE2PARSE !~ /\.svn|lib\/yui.*|lib\/fpdf/ ){

      if ( $FILE2PARSE =~ /.*\.class\.php$/ ){
        open (FILE, $FILE2PARSE);
        while ( $LINE = <FILE> ){

          if ( $LINE =~ /\s*class (\w*)$/ ){
            #print "Found a class : $1 in $FILE2PARSE\n";
            $CLASSES{$1}{'file'} = $FILE2PARSE;
            $CURCLASS = $1;
          }

          if ( $LINE =~ /\s*class (\w*) extends (\w*)$/ ){
            #print "Found a subclass : $1 for $2 in $FILE2PARSE\n";
            $CLASSES{$2}{'extended'}{$1}{'file'} = $FILE2PARSE;
            ($CURCLASS, $EXTCURCLASS) = ($2, $1);
            
          }

          if ( $CURCLASS ne "" && $LINE =~ /.*function\s*(.*)\(.*$/ ){
            $CURMETHOD=$1;
            if ( $CURMETHOD !~ /^__/ ){
              #print "Found function $CURFUNCTION for $CURCLASS \n";
              #$CLASSES{$CURCLASS}{$CURMETHOD} = 0;
              $CLASSES{$CURCLASS}{$CURMETHOD}{'counter'}=0;
            }
            
          }

        }

        close (FILE);
        $CURCLASS = "";
        $EXTCURCLASS = "";

      }

      else {

        open (FILE, $FILE2PARSE);
        while ( $LINE = <FILE> ){

          #if ( $LINE !~ /\s*(\/\/|\/\*|\*\/)/ && $LINE =~ /.*function\s*(.*)\(.*$/ && $LINE !~ /.* = function\(.*\)\{..*$/){
          if ( $LINE !~ /\s*(\/\/|\/\*|\*\/)/ && $LINE =~ /.*function\s*(\w*)\s*\(.*$/i && $LINE !~ /.* = function\(.*\)\{..*$/){
            $CURFUNCTION = $1;
            $FUNCTIONS{$CURFUNCTION}{'file'} = $FILE2PARSE;
            $FUNCTIONS{$CURFUNCTION}{'counter'} = 0;
            #print "Found function : $CURFUNCTION.\n$FILE2PARSE : $LINE\n";
          }
          $CURFUNCTION = "";
        }



      }

    }
      
  }


}

sub wanted {

  if ( $_ =~ /.*\.php$/ ){

    if ( $_ !~ /\.svn|lib\/yui.*|setup.php|\/lib\/(fpdf|crypt)/ ){
      #print "Working on : ".$_."\n";
      $FILE2PARSE=$_;
      open (FILE, $FILE2PARSE);
      $current_line = 0;
      while ($LINE = <FILE>){
        ## lookup for function call
        $current_line++;
        chomp $LINE;
        if ( $LINE !~ /\s*(\/\/|\/\*|\*\/)/ ){

          # if ( $LINE =~ /global \$([a-z0-9_-]*)\;/i ){
          #   OBJ
          # }

          if ( $LINE =~ /\$([a-z0-9_-]*)\s?=\s?new ([A-Z0-9_-]*)\(/i ){
            $CUROBJ = $1;
            $CURCLASS = $2;
            #print "Object : Found an object creation $CUROBJ based on class $CURCLASS (Line : $current_line ($LINE) in $FILE2PARSE)\n";
            push (@OBJ_LIST, $CUROBJ);
            if ( !(exists($CLASSES{$CURCLASS})) ){
              print "Object : the class $CURCLASS in file $FILE2PARSE is unknown in the class definition\n"
            }
            # else{
            #   print "Object : the class $CURCLASS in file $FILE2PARSE is known in the class definition\n";
            # }

            if ( !(exists($CLASSES{$CURCLASS})) ){
              print "Object : the class $CURCLASS is unknown, but it's used in $FILE2PARSE at line $current_line ($LINE)\n";
            }
            $CLASSREL{$CUROBJ}=$CURCLASS;
            $CUROBJ = "";
            $CURCLASS = "";
          }

          if ( $LINE =~ /\$([a-z0-9_-]*)->([a-z0-9_-]*)/i && $LINE !~ /\$this->([a-z0-9_-]*)/i ){
            $CUROBJ = $1;
            $CURMETHOD = $2;
            #print "Object : Found an object call (Line : $current_line ($LINE) in $FILE2PARSE)\n";
            #print "Object : $FILE2PARSE : $CUROBJ -> $CURMETHOD\n";
            if ( !(grep(/$CUROBJ/, @OBJ_LIST)) ){
              print "Object : Unkown object $CUROBJ in $FILE2PARSE at line $current_line\n"; 
            }
            else{
              $CLASSUSAGE{$CLASSREL{$CUROBJ}}{$CURMETHOD}{$FILE2PARSE}{$current_line} = 1;
              $CLASSUSAGE{$CLASSREL{$CUROBJ}}{$CURMETHOD}{'counter'}++;
            }
          }

          # if ( $LINE !~ /\s*(\/\/|\/\*|\*\/)/ && $LINE =~ /.*=.([0-9a-z_-]*)\s.\(.*\);/i ){
          if ( $LINE !~ /\s*( = db_query\s*\(")|\$sql_query = |\$query = "|(.*->.*|^\s*(this|document)\..*;$)|.* = new .*;/ && $LINE =~ /\s*([0-9a-z_-]*)\s*\(.*\).*;$/i ){
            $CURFUNCTION = $1;
            if ( !(grep(/$CURFUNCTION/, @IGNFUNCTION)) && $CURFUNCTION ne "" ){

              if ( exists($FUNCTIONS{$CURFUNCTION}) ){
                #print "Function : $CURFUNCTION on $LINE\n";
                #print FILEOUT "   \"$CURFUNCTION\" -> \"$FILE2PARSE\" [ label = \"line $current_line\" ];\n";
                $FCTUSAGE{$CURFUNCTION}{$FILE2PARSE}{$current_line} = 1;
                $FUNCTIONS{$CURFUNCTION}{'counter'}++;
              }
              else {
                print "found an unkown function ($CURFUNCTION) in file $FILE2PARSE line $current_line ($LINE)\n";
                #print "unkown function in : $LINE\n\n";
              }

            }
            $CURFUNCTION = "";
            
          }


        }


      }
      @OBJ_LIST = ();
      close (FILE);

    }
      
  }
  

}

