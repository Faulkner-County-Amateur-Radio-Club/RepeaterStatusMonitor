Create table repeaterState (id int(6) unsigned auto_increment primary key, repeater varchar(255), powerOn bool, batteryGood bool, reportingOnTime bool);

insert into repeaterState (repeater,powerOn,batteryGood,reportingOnTime) values ('W5AUU-1',1,1,1);
insert into repeaterState (repeater,powerOn,batteryGood,reportingOnTime) values ('W5AUU-2',1,1,1);
insert into repeaterState (repeater,powerOn,batteryGood,reportingOnTime) values ('W5AUU-3',1,1,1);

select * from repeaterState;