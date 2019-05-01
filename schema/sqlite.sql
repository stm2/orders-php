/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Author:  Enno
 * Created: May 1, 2019
 */

PRAGMA user_version = 1;

CREATE TABLE IF NOT EXISTS `submission`
(
    `id` INTEGER PRIMARY KEY,
    `status` INTEGER NOT NULL DEFAULT 0,
    `time` TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `filename` VARCHAR(128) NOT NULL,
    `email` VARCHAR(128)
);
