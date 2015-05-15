#!/usr/bin/perl
use strict;
use open qw(:std :utf8);

#in: maplist

my ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime(time);
my $date = sprintf("%d.%d.%04d", $mday, $mon+1, $year+1900);

print "Einige Zauber und Funktionen in Freewar verraten die aktuelle Position eines Charakters in Form von Koordinaten. Gerade bei Feldern, die nicht zur oberirdischen Hauptlandmasse von Freewar geh\N{U+0026}ouml;ren, ist es oft schwer, herauszufinden, zu welchem Gebiet diese Koordinaten geh\N{U+0026}ouml;ren.<br />Die folgende Liste hilft dabei. Alle Koordinaten sind in der Form '''X''','''Y''' unter dem Namen des Gebiets gelistet, zu dem sie geh\N{U+0026}ouml;ren. So kann mit der Suchfunktion des Browsers leicht das Gebiet zu einer bestimmten Position ermittelt werden.<br />Die Liste ist automatisch aus den Wiki-Kartendaten erstellt (Stand $date) und wird evtl. bei Karten\N{U+0026}auml;nderungen oder Fehlern auch automatisch wieder neu generiert; \N{U+0026}Auml;nderungen an der Liste sind nicht sinnvoll. Stattdessen, wenn etwas auff\N{U+0026}auml;llt, bitte auf der Diskussionsseite vermerken.<br />";

my %areas;
while(<>) {
    next if (/^GET/);
    my($area, $accessible, $x, $y, $rest) = split(/;/);
    next unless($accessible);
    $areas{$area} = () if (!exists($areas{$area}));
    push(@{$areas{$area}}, "$x,$y");
}

foreach my $area (sort(keys(%areas))) {
    gendump($area, $areas{$area});
}

sub gendump
{
    my($area, $coords)=@_;
    print "<!--\n-->{{\N{U+00DC}berschriftensimulation 2|1={{Gebietslink|$area}} (".@{$coords}." Felder)}}";
    print join("; ", @{$coords});
}

print "[[Kategorie:Allgemeines]][[Kategorie:Karten|!Koordinaten (Liste)]]\n";