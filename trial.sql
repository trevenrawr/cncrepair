
------------------------------------------------------------------------------


CREATE VIEW outgoing AS
SELECT invoiceitems.*,
customers.name AS name,
itemtypes.modelnum AS modelnum,
itemtypes.make AS make,
itemtypes.description AS description
FROM ((invoices INNER JOIN invoiceitems ON invoies.id=invoiceitems.inv_id)
INNER JOIN customers ON invoices.billto=customers.id)
INNER JOIN itemtypes ON invoiceitems.itemtype_id=itemtypes.id
WHERE invoiceitems.shipped=0;


CREATE VIEW quantity_verify;

LOAD DATA LOCAL INFILE 'C:/itemlist.txt' IGNORE INTO TABLE itemtypes
FIELDS TERMINATED BY ';;' (make, modelnum, description, repairrate);

------------------------------------------------------------------------------

CREATE VIEW IF NOT EXISTS exchs AS SELECT modelnum, REPLACE(modelnum, ' EXCH', '') as mnum, repairrate, id FROM itemtypes WHERE modelnum LIKE '% exch'

REPLACE(modelnum, ' EXCH', '')

UPDATE itemtypes, exchs SET itemtypes.exchrate = exchs.repairrate, itemtypes.exch=1, exchs.modelnum=CONCAT('blah ', exchs.modelnum) WHERE itemtypes.modelnum=exchs.mnum

DELETE FROM exchs WHERE exchs.mnum=itemtypes.modelnum