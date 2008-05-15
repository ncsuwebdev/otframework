/**
* Chart class
* Create plots from table objet with plootr: http://utils.softr.net/plootr/
* Author: Patrice Ferlet (metal3d@copix.org)
* Licence: MIT
*/
Chart = new Class({
    labels: '',
    datas: '',
    target: '',
    legends: [],
    initialize: function(options){
        //console.log('init')
        this.labels = new Array();
        this.datas = new Array();
        this.options = Object.extend(this.options, options || {});
    },
    options: {
                padding: {left: 30, right: 0, top: 10, bottom: 30},
                barOrientation: 'vertical',
                colorScheme: 'blue'
    },
    setDatas: function (id){
        this.target = id;
        var ob;
        var obname;
        var datas = {};
        var fulldatas = new Array();
        var legends = new Array();
        $$('#'+id+' tr').each(function (el,i){
            if(i==0){
                //header
                ob = new Array();
                j=0;
                el.getElementsBySelector('th').each(function(th){
                     //console.debug(th);
                     if(th.getText()!=""){          
                        ob[j] ={v:j, label: th.getText()};
                        j++;
                     }                      
                });
            }else{
                obname = '';
                try{
                    var Zobname = el.getElementsBySelector('th')[0].getText();
                    if(Zobname!=""){
                        obname = Zobname.replace(/[^a-zA-Z0-9]/,'_');
                    }else{
                        obname = 'data_'+i;
                    }
                    
                }catch(e){
                    obname = 'data_'+i;
                }
                legends.push(obname);
                eval('datas.'+obname+'=new Array();');
                el.getElementsBySelector('td').each(function(td,k){
                    //console.log(td);
                    eval('datas.'+obname+'.push([k,td.getText().toInt()]);');   
                });
            }
        });
        this.datas = datas; 
        this.labels = ob;
        this.legends = legends;
        //console.debug(this);
    },
    createScheme: function (){
        if(this.options.colors){
            colorScheme= new Hash();
            for (i in this.options.colors){
                colorScheme.set(this.legends[i],this.options.colors[i]);
            }
            this.options.colorScheme = colorScheme.obj;
        }       
    },
    render: function(where){
        this.options.xTicks = this.labels;
        this.createScheme();
        if(!this.options.type||this.options.type=='bars'){
            var plot = new Plotr.BarChart(where,this.options);
        }else if(this.options.type=='pie'){
            var plot = new Plotr.PieChart(where,this.options);
        }else{          
            var plot = new Plotr.LineChart(where,this.options);
        }
        plot.addDataset(this.datas);
        //console.log('ok');
        plot.render();
        if(this.options.legend==true){
            plot.addLegend(where);
        }
        plot.reset();
        plot = null;
    }
});

Element.extend({
    toChart: function(options){
        this.options = Object.extend({
            type:'bars',
            width: 550,
            height: 350,
            showInSide:true,
            shouldFill: true
        }, options || {});
        
        if(this.getTag()!="table"){
            throw 'toChart() only works with tables !'
        }
        
        //redo the Scheme      
        
        var canvas = new Element('canvas').setProperties({
            width: this.options.width,
            height: this.options.height
        })
        canvas.id='chartfor'+this.id 
        
        var container = new Element('div').injectAfter(this);
        canvas.injectInside(container);
        
        var c = new Chart(this.options);
        c.setDatas(this.id);
        //console.debug(c);
        
        c.render(canvas.id)
        id = this.id
        this.remove();
        container.id = id
    }
});
