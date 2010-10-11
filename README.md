#node spreadsheet
this is a solution to read excel files in node js
based on PHPExcel from phpexcel.codeplex.com
php based is better then nothing

#example
    var spreadsheet=require('node_spreadsheet')
    spreadsheet.read(__dirname+"/Book1.xls",function(obj){console.log(obj);});
output:
    [ [ 'phonecol', 'ansicol', 'utfcol', 'numcol', 'datecol' ]
    , [ '0547490305'
      , 'asfasd'
      , '\u05d2\u05d3\u05db\u05e9\u05d3\u05d2\u05e9\u05d3\u05d2'
      , 12312
      , Sun, 10 Oct 2010 10:55:00 GMT
      ]
    ]

#requirements:
this pice of software is based on php5-cli so you need to install it first.
like :

sudo aptitude install php5-cli
