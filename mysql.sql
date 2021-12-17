sql_query_pre = DROP TABLE IF EXISTS page_ocdlasearch2;
sql_query_pre = CREATE TABLE page_ocdlasearch2 LIKE page;
sql_query_pre = INSERT INTO page_ocdlasearch2 SELECT * FROM page;
sql_query_pre = ALTER TABLE page_ocdlasearch2 DEFAULT CHARACTER SET=utf8;
sql_query_pre = ALTER TABLE page_ocdlasearch2 DROP INDEX name_title;
sql_query_pre = ALTER TABLE page_ocdlasearch2 MODIFY page_title VARCHAR(255);
sql_query_pre = DROP TABLE IF EXISTS page_ocdlasearch;
sql_query_pre = RENAME TABLE page_ocdlasearch2 TO page_ocdlasearch;