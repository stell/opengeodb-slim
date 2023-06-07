# opengeodb-slim
PHP Generierungs-Script für die Erstellung eines reduzierten opengeodb MySQL Dumps
Einige Erklärungen zum OpenGeoDB Datenbank Schema: http://opengeodb.giswiki.org/wiki/Datenbank

## Schritte zur Generierung von zwei schlanken MySQL OpenGeoDB Tabellen

1. Hol Dir die OpenGeoDB tab Dateien von https://github.com/ratopi/opengeodb
2. Benenne sie nach den KFZ Zeichen des Landes um. DE.tab => D.tab, LI.tab => FL.tab, etc.
3. Starte gen.php !
4. Nach der Generierung hast Du zwei CSV Dateien pro Land. Z.B. D_zips.csv und D_locations.CSV für Deutschland.
5. Erstelle zwei MYSQL Tabellen mit folgenden Abfragen:
```
CREATE TABLE `geo_locations` (
  `loc_id` int(11) NOT NULL,
  `loc_kfz` char(3) NOT NULL,
  `loc_name` varchar(255) NOT NULL,
  `loc_lat` double NOT NULL,
  `loc_lon` double NOT NULL,
  `level` int(11) NOT NULL,
  `hier2` int(11) NOT NULL,
  `hier3` int(11) NOT NULL,
  `hier4` int(11) NOT NULL,
  `hier5` int(11) NOT NULL,
  `hier6` int(11) NOT NULL,
  `hier7` int(11) NOT NULL,
  `hier8` int(11) NOT NULL,
  `hier9` int(11) NOT NULL,
  `einw` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;


ALTER TABLE `geo_locations`
  ADD KEY `einw` (`einw`),
  ADD KEY `loc_id` (`loc_id`),
  ADD KEY `loc_kfz` (`loc_kfz`),
  ADD KEY `loc_name` (`loc_name`),
  ADD KEY `level` (`level`),
  ADD KEY `hier2` (`hier2`),
  ADD KEY `hier3` (`hier3`),
  ADD KEY `hier4` (`hier4`),
  ADD KEY `hier5` (`hier5`),
  ADD KEY `hier6` (`hier6`),
  ADD KEY `hier7` (`hier7`),
  ADD KEY `hier8` (`hier8`),
  ADD KEY `hier9` (`hier9`);


CREATE TABLE `geo_zips` (
  `loc_id` int(11) NOT NULL,
  `loc_kfz` char(3) NOT NULL,
  `loc_plz` varchar(10) NOT NULL,
  `loc_name` varchar(255) NOT NULL,
  `loc_lat` double NOT NULL,
  `loc_lon` double NOT NULL,
  `level` int(11) NOT NULL,
  `hier2` int(11) NOT NULL,
  `hier3` int(11) NOT NULL,
  `hier4` int(11) NOT NULL,
  `hier5` int(11) NOT NULL,
  `hier6` int(11) NOT NULL,
  `hier7` int(11) NOT NULL,
  `hier8` int(11) NOT NULL,
  `hier9` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;


ALTER TABLE `geo_zips`
  ADD KEY `loc_id` (`loc_id`),
  ADD KEY `loc_kfz` (`loc_kfz`),
  ADD KEY `loc_plz` (`loc_plz`),
  ADD KEY `loc_name` (`loc_name`),
  ADD KEY `level` (`level`),
  ADD KEY `hier2` (`hier2`),
  ADD KEY `hier3` (`hier3`),
  ADD KEY `hier4` (`hier4`),
  ADD KEY `hier5` (`hier5`),
  ADD KEY `hier6` (`hier6`),
  ADD KEY `hier7` (`hier7`),
  ADD KEY `hier8` (`hier8`),
  ADD KEY `hier9` (`hier9`);
```
6. Importiere die generierten CVS Dateien in diese Tabellen
7. Et voilà!

## Einige Abfragen

+ Alle Postleitzahlen in Berlin
```
SELECT loc_plz  FROM `geo_zips` WHERE `loc_name` LIKE 'Berlin'
oder
SELECT loc_plz  FROM `geo_zips` WHERE `loc_id` = 14356
```

+ Alle Landkreise in Deutschland
```
SELECT * FROM `geo_locations` WHERE `level` = 5 AND loc_kfz = 'D'
or
SELECT * FROM `geo_locations` WHERE `level` = 5 AND hier2 = 105
```

+ Alle Postleitzahlen im 50 Km Radius um Berlin
```
SELECT loc_plz FROM `geo_zips` WHERE (
ACOS(SIN(PI() * 52.520008 / 180.0) * SIN(PI() * loc_lat / 180.0) 
+ COS(PI() * 52.520008/180.0) * COS(PI() * loc_lat / 180.0) 
* COS(PI() * loc_lon / 180.0 - PI() * 13.404954 / 180.0)) * 6371 )
< 50;
```

+ Alle Städte (level 7) um Passau (48.566736, 13.431947) in einem 20 km Radius
```
SELECT loc_name FROM `geo_locations` WHERE (
ACOS(SIN(PI() * 48.566736 / 180.0) * SIN(PI() * loc_lat / 180.0) 
+ COS(PI() * 48.566736/180.0) * COS(PI() * loc_lat / 180.0) 
* COS(PI() * loc_lon / 180.0 - PI() * 13.431947 / 180.0)) * 6371 )
< 20 AND level = 7;
```

+ Die Geo-Hierarchie der Stadt "Sinzendorf" (locid = 132446)
```
SELECT l2.loc_name AS land, 
l3.loc_name as bundesland,
l4.loc_name as bezirk,
l5.loc_name as landkreis,
l6.loc_name as gemeinde,
l7.loc_name as ortschaft
FROM `geo_locations` AS l7
LEFT JOIN geo_locations AS l6 ON l7.hier6=l6.loc_id
LEFT JOIN geo_locations AS l5 ON l6.hier5=l5.loc_id
LEFT JOIN geo_locations AS l4 ON l5.hier4=l4.loc_id
LEFT JOIN geo_locations AS l3 ON l4.hier3=l3.loc_id
LEFT JOIN geo_locations AS l2 ON l3.hier2=l2.loc_id
WHERE l7.loc_id = 132446
```
