import { Component, ViewChild } from '@angular/core';
import { IonicPage, NavController, Platform, ToastController, NavParams } from 'ionic-angular';
import { TranslateService } from '@ngx-translate/core';
import { Chart } from 'chart.js';
import { MenuProvider, AppProvider, Online } from '../../providers/providers';

export interface pieObject
{
  active:any
  inactive:any
}

@IonicPage()
@Component({
selector: 'page-piecart',
templateUrl: 'piecart.html',
})
export class PiecartPage 
{
  _dataPie: pieObject;
  _dataGraph: any;
  _dataTitle: any;
  _dataTitleTop: any;
  _thisParam: any;
  _trueText: string;
  _falseText: string;
  graphMonth: any[];
  pieChart: any;
  horizonChart: any;

  @ViewChild('pieCanvas') pieCanvas;
  @ViewChild('horizonCanvas') horizonCanvas;

  constructor(
    private app: AppProvider, 
    private online: Online, 
    public translate: TranslateService, 
    public toastCtrl: ToastController,
    public navParam: NavParams, 
    public platform: Platform, 
    public menuProv: MenuProvider,
    public navCtrl: NavController) 
  {
    this._thisParam = this.navParam.get('paramTabs');

    this.translate.get(['BOOLEAN_TRUE', 'BOOLEAN_FALSE', 'GRAPH_MONTH']).subscribe((value) => {
      this._trueText = value.BOOLEAN_TRUE;
      this._falseText = value.BOOLEAN_FALSE;
		  this.graphMonth = value.GRAPH_MONTH;
    });
  }

  ngAfterViewInit()
  {
    //setTimeout(() => {}, 150);
    this.getAppData();
  }

	async getAppData()
	{
    let toast1 = this.toastCtrl.create({
      message: 'Load data ...',
      position: 'top'
		});
		toast1.present();
		await this.app.graphdetail({filter:this._thisParam}).subscribe((res: any) => {
      //let nameParam = this._thisParam;
      const itemData = res.graphDetailApp.items;
			this._dataPie = itemData.pie;
			this._dataGraph = itemData.graph;
			this._dataTitle = itemData.title;
			this._dataTitleTop = this._dataTitle.others.title + ' ' + this._dataTitle.top;

      setTimeout(() => {
        this.buildPieChart();
        this.buildHorizonChart();
        toast1.dismiss();
      }, 1000);
      //console.log(this._dataGraph)
		},
		(err) => {
			this._dataPie = null;
      this._dataGraph = null;
      this._dataTitle = null;
			toast1.dismiss();
			let toast2 = this.toastCtrl.create({
				duration: 6000,
				message: 'Ups... Error',
				position: 'top'
			});
      toast2.present();
      this.online.checkOnline(false);
		})
	}

  buildPieChart(data?: any)
  {
    //console.log(this._dataTitle, this._dataGraph, this._dataPie);
    this.pieChart = new Chart(this.pieCanvas.nativeElement, {
      type: 'pie',
      data: {
        labels: [this._trueText, this._falseText],
        datasets: [{
          label: '# Total',
          fill: true, 
          data: [this._dataPie.active, this._dataPie.inactive],
          backgroundColor: [
            'rgba(54, 162, 235, 0.9)',
            'rgba(255, 99, 132, 0.9)'
          ],
          hoverBackgroundColor: [
            "#36A2EB",
            "#FF6384"
          ]
        }]
      },
			options: {
        title: {
          display: true,
          text: this._dataTitle.others.title_compare + ' ' + this._dataTitle.top
        },
				responsive: true
			}
    });
  }

  buildHorizonChart()
  {
    this.horizonChart = new Chart(this.horizonCanvas.nativeElement, {
      type: 'bar',
      data: {
        labels: this.graphMonth,
        datasets: [{
          label: '# Total',
          fill: true, 
          data: this._dataGraph,
          backgroundColor: 'rgba(75,192,192,0.4)',
          hoverBackgroundColor: 'rgba(220,220,220,1)'
        }]
      },
			options: {
        legend: { display: false },
        title: {
          display: true,
          text: this._dataTitle.others.title_predictive + ' ' + this._dataTitle.top
        },
				responsive: true
			}
    });
  }
}
