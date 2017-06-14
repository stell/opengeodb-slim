# opengeodb-slim

## Diese Anleitung gibt es auch in [Deutsch](https://github.com/stell/opengeodb-slim/blob/master/README.de.md).

PHP generation tool for creating simple opengeodb MySQL dump  
Some explanation about the OpenGeoDB database scheme: http://opengeodb.giswiki.org/wiki/Datenbank (in german)

## Steps for generating two slim MySQL OpenGeoDB Tables

1. Get OpenGeoDB tab files from http://www.fa-technik.adfc.de/code/opengeodb/
2. Rename them to the license plate of the country. DE.tab => D.tab, LI.tab => FL.tab, etc.
3. Start gen.php !
4. After generation you have two CSV files per country called D_zips.csv and D_locations.CSV for Germany.
5. Create two MYSQL tables using following queries:
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
6. Import the generated CVS files into the tables
7. Et voilà!

## Doing some queries

+ Get all ZIPs in Berlin
```
SELECT loc_plz  FROM `geo_zips` WHERE `loc_name` LIKE 'Berlin'
or
SELECT loc_plz  FROM `geo_zips` WHERE `loc_id` = 14356
```

+ Get all districts in Germany
```
SELECT * FROM `geo_locations` WHERE `level` = 5 AND loc_kfz = 'D'
or
SELECT * FROM `geo_locations` WHERE `level` = 5 AND hier2 = 105
```

+ Get all ZIPs in a 50 km radius around Berlin
```
SELECT loc_plz FROM `geo_zips` WHERE (
ACOS(SIN(PI() * 52.520008 / 180.0) * SIN(PI() * loc_lat / 180.0) 
+ COS(PI() * 52.520008/180.0) * COS(PI() * loc_lat / 180.0) 
* COS(PI() * loc_lon / 180.0 - PI() * 13.404954 / 180.0)) * 6371 )
< 50;
```

+ get all cities (level 7) around Passau (48.566736, 13.431947) in a 20 km radius
```
SELECT loc_name FROM `geo_locations` WHERE (
ACOS(SIN(PI() * 48.566736 / 180.0) * SIN(PI() * loc_lat / 180.0) 
+ COS(PI() * 48.566736/180.0) * COS(PI() * loc_lat / 180.0) 
* COS(PI() * loc_lon / 180.0 - PI() * 13.431947 / 180.0)) * 6371 )
< 20 AND level = 7;
```

+ get Geo-Structures of the city "Sinzendorf" (locid = 132446)
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
