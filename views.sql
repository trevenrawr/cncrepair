USE cncrepair;

CREATE VIEW specific_items AS
(SELECT itemtypes.modelnum,
itemtypes.make,
0 AS assembly,
items.id,
specificassemblies.parent,
items.serial,
items.barcode,
cust_location.name AS atcust,
items.atcustomer,
items.status+0 AS status,
items.status AS txtstatus,
items.onhold,
items.itemtype_id,
items.rack,
items.shelf,
items.stock,
items.owner AS owner_id,
cust_owner.name AS owner,
DATE_FORMAT(items.lastseen, "%b %d, %Y %T") AS timeready,
items.readyfor,
items.priority+0 AS priority,
DATE_FORMAT(items.lastseen, "%b %d, %Y") AS lastseen
FROM (((items INNER JOIN itemtypes ON items.itemtype_id=itemtypes.id)
LEFT JOIN customers AS cust_owner ON items.owner=cust_owner.id)
LEFT JOIN customers AS cust_location ON items.atcustomer=cust_location.id)
LEFT JOIN specificassemblies ON items.id=specificassemblies.child)
UNION
(SELECT itemtypes.modelnum,
itemtypes.make,
1 AS assembly,
0 AS id,
phantomitems.id AS parent,
'' AS serial,
'' AS barcode,
'' AS atcust,
0 AS atcustomer,
'' AS status,
'' AS txtstatus,
'' AS onhold,
phantomitems.itemtype_id,
'' AS rack,
'' AS shelf,
'' AS stock,
'' AS owner_id,
'' AS owner,
'' AS timeready,
'' AS readyfor,
'' AS priority,
'' AS lastseen
FROM phantomitems INNER JOIN itemtypes ON phantomitems.itemtype_id=itemtypes.id)
ORDER BY parent ASC, assembly DESC, modelnum;

CREATE VIEW incoming AS
(SELECT quoteitems.id,
quoteitems.quote_id,
quoteitems.itemtype_id,
quoteitems.type,
quoteitems.quantity,
quoteitems.print,
quoteitems.description,
customers.name,
itemtypes.modelnum,
itemtypes.make
FROM ((quotes INNER JOIN quoteitems ON quotes.id=quoteitems.quote_id)
INNER JOIN customers ON quotes.billto=customers.id)
INNER JOIN itemtypes ON quoteitems.itemtype_id=itemtypes.id
WHERE TIMESTAMPDIFF(DAY, quotes.created, CURRENT_TIMESTAMP()) < 30)
UNION
(SELECT 0,
0,
itemtype_id,
'owed',
1,
'regular',
description,
name,
modelnum,
make
FROM owed_item_list);

CREATE VIEW quote_view AS
SELECT quoteitems.*,
quotes.billto,
customers.name,
quotes.phone_id,
quotes.email_id,
quotes.total,
quotes.created,
quotes.sent,
quotes.itemtotal,
quotes.notes,
quotes.publicnotes,
quotes.purchaseorder,
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
itemtypes.salerate
FROM (((quotes INNER JOIN quoteitems ON quotes.id=quoteitems.quote_id)
INNER JOIN customers ON quotes.billto=customers.id)
LEFT JOIN users ON users.id=quotes.createdby)
INNER JOIN itemtypes ON quoteitems.itemtype_id=itemtypes.id;

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

CREATE VIEW history_log AS
SELECT histories.id,
histories.item_id,
items.itemtype_id,
iitems.inv_id,
histories.invoiceitem_id,
iitems.type AS service,
siitems.inv_id as ship_inv_id,
histories.ship_invoiceitem_id,
camefrom AS cust_from_id,
customers.name AS camefrom,
histories.shippedto,
DATE_FORMAT(histories.unpacked, "%b %d, %Y") AS unpacked,
DATE_FORMAT(histories.received, "%b %d, %Y %T") AS received,
DATE_FORMAT(histories.cleaned, "%b %d, %Y %T") AS cleaned,
DATE_FORMAT(histories.repaired, "%b %d, %Y %T") AS repaired,
DATE_FORMAT(histories.tested, "%b %d, %Y %T") AS tested,
histories.testedok,
DATE_FORMAT(histories.shipped, "%b %d, %Y") AS shipped,
histories.carrier,
histories.trackingnum
FROM (((histories INNER JOIN items ON histories.item_id=items.id)
LEFT JOIN customers ON histories.camefrom=customers.id)
LEFT JOIN invoiceitems AS iitems ON histories.invoiceitem_id=iitems.id)
LEFT JOIN invoiceitems AS siitems ON histories.ship_invoiceitem_id=siitems.id
ORDER BY histories.received DESC;

CREATE VIEW notes_view AS
SELECT notes.*,
users.name,
DATE_FORMAT(notes.time_noted, "%b %d, %Y") AS note_date,
histories.item_id
FROM (notes INNER JOIN users ON notes.user_id=users.id)
INNER JOIN histories ON notes.history_id=histories.id
WHERE histories.shipped = 0
ORDER BY notes.time_noted DESC;

CREATE VIEW assembly_view AS
SELECT assemblies.*,
itemtypes.*
FROM assemblies INNER JOIN itemtypes ON assemblies.child=itemtypes.id;

CREATE VIEW specificassembly_view AS
SELECT specificassemblies.*,
itemtypes.modelnum,
itemtypes.make,
itemtypes.assembly,
items.id,
items.serial,
items.barcode,
items.atcustomer,
items.status+0 AS status,
items.status AS txtstatus,
items.itemtype_id,
items.rack,
items.shelf,
items.stock,
items.owner AS owner_id,
DATE_FORMAT(items.lastseen, "%b %d, %Y %T") AS timeready,
items.readyfor,
items.priority+0 AS priority,
DATE_FORMAT(items.lastseen, "%b %d, %Y") AS lastseen
FROM (specificassemblies INNER JOIN items ON specificassemblies.child=items.id)
INNER JOIN itemtypes ON items.itemtype_id=itemtypes.id;

CREATE VIEW rec_inv_list AS
SELECT items.id AS item_id,
items.itemtype_id,
customers.name AS billto,
invoiceitems.id AS invoiceitem_id,
invoiceitems.inv_id,
invoiceitems.type,
DATE_FORMAT(invoices.created, "%b %d, %Y") AS created,
invoices.shipname,
(invoiceitems.quantity - invoiceitems.qtyreceived) AS qtyremaining
FROM ((items INNER JOIN invoiceitems ON items.itemtype_id=invoiceitems.itemtype_id)
INNER JOIN invoices ON invoiceitems.inv_id=invoices.id)
INNER JOIN customers ON invoices.billto=customers.id
ORDER BY invoices.created DESC;

CREATE VIEW ship_inv_list AS
SELECT items.id AS item_id,
items.itemtype_id,
customers.name AS billto,
invoiceitems.id AS invoiceitem_id,
invoiceitems.inv_id,
invoiceitems.type,
DATE_FORMAT(invoices.created, "%b %d, %Y") AS created,
invoices.shipname,
(invoiceitems.quantity - invoiceitems.shipped) AS qtyremaining
FROM ((items INNER JOIN invoiceitems ON items.itemtype_id=invoiceitems.itemtype_id)
INNER JOIN invoices ON invoiceitems.inv_id=invoices.id)
INNER JOIN customers ON invoices.billto=customers.id
ORDER BY invoices.created DESC;

CREATE VIEW owed_item_list AS
SELECT oweditems.*,
customers.name,
customers.id AS cust_id,
itemtypes.modelnum,
itemtypes.make,
itemtypes.description,
items.barcode,
DATE_FORMAT(TIMESTAMPADD(DAY, 15, oweditems.shipped), "%b %d, %Y") AS due_date
FROM (((oweditems INNER JOIN invoices ON oweditems.inv_id=invoices.id)
INNER JOIN customers ON invoices.billto=customers.id)
INNER JOIN itemtypes ON oweditems.itemtype_id=itemtypes.id)
LEFT JOIN items ON oweditems.item_sent=items.id;

CREATE VIEW quote_search AS
(SELECT DISTINCT quote_id AS refnum,
name,
created AS ts,
createdby,
"" AS purchaseorder,
DATE_FORMAT(created, "%b %d, %Y") AS created,
DATE_FORMAT(sent, "%b %d, %Y") AS sent,
total,
itemtotal,
"Quote" AS type FROM quote_view)
UNION
(SELECT DISTINCT inv_id AS refnum,
name,
createdby,
created AS ts, purchaseorder,
DATE_FORMAT(created, "%b %d, %Y") AS created,
DATE_FORMAT(sent, "%b %d, %Y") AS sent,
total,
itemtotal,
"Invoice" AS type FROM invoice_view);