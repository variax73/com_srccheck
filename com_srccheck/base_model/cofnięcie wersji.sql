SELECT * FROM joomla.dev_schemas where extension_id = ( SELECT extension_id FROM joomla.dev_extensions where element = 'com_srccheck');
UPDATE joomla.dev_extensions SET manifest_cache = replace(manifest_cache, '1.0.3','1.0.2') where element = 'com_srccheck';
update joomla.dev_schemas SET version_id = '1.0.2' where extension_id = ( SELECT extension_id FROM joomla.dev_extensions where element = 'com_srccheck');
drop table dev_crc_files_has_trustedarchive;
drop table dev_crc_trustedarchive;
commit;
SELECT manifest_cache FROM joomla.dev_extensions where element = 'com_srccheck';
SELECT * FROM joomla.dev_schemas;