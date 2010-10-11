var fs=require('fs'), path=require('path'), exec  = require('child_process').exec;

var that=this;    
that.tempdir='/tmp/';
var lastcommand
this.tryshm = function()
{
 path.exists('/dev/shm/',function(exists) // try shm
 {
  if(exists)
  {
   path.exists('/dev/shm/node-spreadsheet',function(exists)
   {
    if(exists)
    {
     that.tempdir='/dev/shm/node-spreadsheet/';
    }
    else
    {
     fs.mkdir('/dev/shm/node-spreadsheet', 0777, function (err)
     {
      if(err) throw err;
      else
       that.tempdir='/dev/shm/node-spreadsheet/'; 
      //callback();
     });
    }
   });
  }
 }); 
}
this.tryshm();


var lastrandom=[]
var lastrandom_time=0;
function uniquerandom()
{
  var newtime=(new Date()).getTime(),random;
  if(lastrandom_time!=newtime&&lastrandom.length>0) lastrandom=[];
  do 
  {
   random=Math.floor(Math.random() * 9999);
  } while (lastrandom.indexOf(random)!=-1);
  lastrandom.push(random);
  return (newtime*10000)+random;
} this.uniquerandom=uniquerandom();

var isoDateReviver_re=/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2}(?:\.\d*)?)(?:([\+-])(\d{2})\:(\d{2}))?Z?$/;
function isoDateReviver(key, value)
{
  if (typeof value === 'string')
  {
    var a = isoDateReviver_re.exec(value);
      if (a) {
        var utcMilliseconds = Date.UTC(+a[1], +a[2] - 1, +a[3], +a[4], +a[5], +a[6]);
        return new Date(utcMilliseconds);
      }
  }
  return value;
}
    
function show_error(error, stdout, stderr)
{
  if(stdout)console.log('node_spreadsheet_stdout: ' + stdout);
  if(stderr)console.log('node_spreadsheet_stderr: ' + stderr);
  if (error !== null)
  {
    console.log('node-spreadsheet last command: ' + lastcommand);
    console.log('node-spreadsheet error: ' + error);
  }
}

var exec_option={timeout:1500};

function read(inputfile,callback,options)
{
  if(!options)options={}
  var file=('file' in options) ? this.tempdir+options['file'] : this.tempdir+uniquerandom()+'.png';

  var args_str='',args=[];
  
  //options usage example
  //if defined then use value:
  //if('indent' in options)       args.push('--indent='+options['indent']);
  //if defined then use boolean:
  //if(('no-auto-dir' in options) && options['no-auto-dir'])    args.push('--no-auto-dir');
  
  args.push('-f'); // add text
  args.push(__dirname+'/convert.php'); // add text  
  args.push(inputfile); // add text
  args.push(file); // save to file only
  
  for(var i=0;i<args.length;i++)
  {
   args_str+=" '"+args[i].replace(/[^\\]'/g, function(m)
   {
    return m.slice(0, 1)+'\\\'';
   })+"'";
  }
  
  var cmd='export LANG=en_US.UTF-8;php '+args_str,exec_option;
  lastcommand=cmd;
  var child = exec(cmd,  show_error );

  //var child = exec('export LANG=en_US.UTF-8;env ',  show_error );
  child.on('exit',function (code, signal)
  {
   if(code==0)
   {
    fs.readFile(file, 'utf-8', function (err, data) 
    {
     fs.unlink(file, function(err2)
     {
      if (err) throw err;
      //if (err2) throw err;
      console.log(data.toString());
      callback(JSON.parse(data,isoDateReviver));
      //callback(eval(data));
     });
    });
   }
   else
    callback([["FILE NOT FOUND"]])
  });
} this.read=read;

//test:
read(__dirname+"/Book1.xls",function(obj){console.log(obj);});
