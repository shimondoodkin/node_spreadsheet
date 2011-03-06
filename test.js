//var spreadsheet=require('node_spreadsheet')
var spreadsheet=require('./index')
function test1(){
console.log("read it as arrays - original:");
spreadsheet.read(__dirname+"/Book1.xls",function(obj){console.log(obj);test2();});
}

function test2()
{
console.log("read it as arrays and convert to objects:");
spreadsheet.readcols(__dirname+"/Book1.xls",[ 'phonecol', 'ansicol', 'utfcol', 'numcol', 'datecol' ],function(obj){console.log(obj);test3();});
}

function test3()
{
console.log("read it as arrays and convert to objects, iterate each object after conversion to object:");
spreadsheet.readcols_each(__dirname+"/Book1.xls",[ 'phonecol', 'ansicol', 'utfcol', 'numcol', 'datecol' ],
function(obj,key,array){
 obj.calculatedfiled=obj.numcol*5;
 // console.log(obj);
},
function(items){console.log(items);}
);
}

test1();

