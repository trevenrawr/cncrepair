create table if not exists htscodes (
	id int unsigned not null auto_increment primary key,
	hts varchar (12) not null default '',
	description text
) ENGINE = INNODB;


-- foreign key not added yet!!!!!!!!!!
alter table itemtypes add  hts int(10) unsigned default null,
alter table itemtypes add  madein varchar(63) not null default '';
ALTER TABLE itemtypes ADD FOREIGN KEY(`hts`) REFERENCES htscodes(id) ON UPDATE CASCADE ON DELETE SET NULL


DROP VIEW invoice_view
CREATE VIEW invoice_view AS
SELECT invoiceitems.*,
invoices.billto,
customers.name,
invoices.phone_id,
invoices.email_id,
invoices.total,
invoices.created,
invoices.sent,
invoices.itemtotal,
invoices.notes,
invoices.publicnotes,
invoices.purchaseorder,
users.name AS createdby,
itemtypes.modelnum,
itemtypes.make,
itemtypes.details,
itemtypes.packing,
itemtypes.assembly,
itemtypes.repair,
itemtypes.repairrate,
itemtypes.exch,
itemtypes.exchrate,
itemtypes.sale,
itemtypes.salerate,
itemtypes.madein,
htscodes.hts
FROM ((((invoices INNER JOIN invoiceitems ON invoices.id=invoiceitems.inv_id)
INNER JOIN customers ON invoices.billto=customers.id)
LEFT  JOIN users ON users.id=invoices.createdby)
INNER JOIN itemtypes ON invoiceitems.itemtype_id=itemtypes.id)
LEFT JOIN htscodes ON itemtypes.hts=htscodes.id;