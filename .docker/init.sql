CREATE USER 'userwww'@'%' IDENTIFIED BY 'haslohaslo123';
GRANT ALL PRIVILEGES ON *.* TO 'userwww'@'%';
FLUSH PRIVILEGES;

CREATE DATABASE eco_flow_history;

user eco_flow_history

CREATE TABLE history
(
    `id`                    int      NOT NULL AUTO_INCREMENT,
    `battery_level`         int      NOT NULL,
    `time_charge_discharge` int      NOT NULL,
    `wats_in`               int      NOT NULL,
    `wats_out`              int      NOT NULL,
    `date_time`             datetime NOT NULL,
    `hour`                  int      NOT NULL,
    `minute`                int      NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE delta_charge
(
    `id`            int  NOT NULL AUTO_INCREMENT,
    `battery_start` int  NOT NULL,
    `battery_now`   int  NOT NULL,
    `counted_wats`  int  NOT NULL,
    `hour`          int DEFAULT NULL,
    `date`          date NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE extra_battery
(
    `id`        int      NOT NULL AUTO_INCREMENT,
    `wats`      int      NOT NULL,
    `date_time` datetime NOT NULL,
    `hour`      int      NOT NULL,
    `minute`    int      NOT NULL,
    PRIMARY KEY (id)
);