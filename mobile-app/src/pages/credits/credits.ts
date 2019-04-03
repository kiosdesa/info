import { Component } from '@angular/core';
import { IonicPage, NavController, LoadingController } from 'ionic-angular';
import { DomSanitizer } from '@angular/platform-browser';
import { RequestApiProvider, Online } from '../../providers/providers';

@IonicPage()
@Component({
	selector: 'page-credits',
	templateUrl: 'credits.html',
})
export class CreditsPage 
{
	_dataDescription: any;
	_dataInfoSystem: any;
	_dataInfoApp: any;
	_dataAboutOwner: any;
	_dataAboutPM: any;
	_dataAboutCreator: any;
	_dataCreatorMap: any;
	_dataAboutImplementor: any;
	_dataAboutCommunity: any;
	_dataVersion: any;

	constructor(
		private sanitizer: DomSanitizer,
		private online: Online,
		private api: RequestApiProvider,
		public navCtrl: NavController, 
		public loading: LoadingController) 
	{}

	ionViewDidEnter() 
	{
	    let loader = this.loading.create({
			spinner: 'dots',
			content: 'Loading...',
	    });

		loader.present();
		this.api.get('app/infokios', 'infokiosApp')
		.subscribe((res: any) => {
		  const theRes = res.infokiosApp;
		  const InfoApp = theRes.items;
		  this._dataDescription = InfoApp.description;
		  this._dataInfoSystem = InfoApp.info.system;
		  this._dataInfoApp = InfoApp.info.app;
		  this._dataAboutOwner = InfoApp.about.owner;
		  this._dataAboutPM = InfoApp.about.project_manager;
		  this._dataAboutCreator = InfoApp.about.creator;
		  this._dataCreatorMap = this.sanitizer.bypassSecurityTrustResourceUrl(InfoApp.about.creator_map);
		  this._dataAboutCommunity = InfoApp.about.community;
		  this._dataVersion = InfoApp.version;
		  loader.dismiss();
		},
		(err) => {
			this._dataDescription = null;
			this._dataInfoSystem = null;
			this._dataInfoApp = null;
			this._dataAboutOwner = null;
			this._dataAboutCreator = null;
			this._dataAboutPM = null;
			this._dataAboutCommunity = null;
			this._dataVersion = null;
			loader.dismiss();
		  	this.online.checkOnline(false);
		})
	}
}