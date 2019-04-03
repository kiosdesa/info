import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams, App } from 'ionic-angular';
import { AppProvider } from '../../providers/providers';

export interface Announ 
{
	title: string
	duration: any
	finish: boolean
	body: any
}

@IonicPage()
@Component({
  selector: 'page-announce',
  templateUrl: 'announce.html',
})
export class AnnouncePage 
{
	_currentData: Announ;
	constructor(
		public appProv: AppProvider, 
		public app: App,
		public navCtrl: NavController, 
		public navParams: NavParams) 
	{
		this.getData();
	}

	getData()
	{
		this.appProv.announce({filter:'page'}).subscribe((res: any) => {
			const annApp = res.announceApp.items;
			this._currentData = annApp;
		});
	}

	openPage(p)
	{}
}
