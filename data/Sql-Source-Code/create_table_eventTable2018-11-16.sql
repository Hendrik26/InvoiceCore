create table event_table
  (row_id integer primary key, -- id of the table-rows
  event_type varchar(30),
  aggregate_id_type varchar(30),
  aggregate_id_string varchar(70),
  date_string varchar(30),
  serialized_event_data varchar(150));