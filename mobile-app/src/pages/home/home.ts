import { Component } from '@angular/core';
import { IonicPage, NavController, Platform, ToastController, NavParams, Events } from 'ionic-angular';
import { TranslateService } from '@ngx-translate/core';
import { MenuProvider, RequestApiProvider, Online, NotifyProvider, SmartAudioProvider } from '../../providers/providers';

export interface SlideBanner 
{
  title: string
  description: string
  image: string
}

export interface CategoryInterface 
{
	id: number
	name: string
	slug: string
	component: any
	cluster?: any
	icon?: string
	index?: number
}

export interface ProductInterface 
{
	id: any
	name: string
	slug: string
	thumb?: string
	fix_price?: string
	sku?: string
	returned?: string
	flash_sale?: string
	component: any
	index?: number
}

@IonicPage()
@Component({
  selector: 'page-home',
  templateUrl: 'home.html',
})
export class HomePage 
{
	slides: SlideBanner[];
	dir: string = 'ltr';
	_cdnBanner: any;
	_cdnIcon: any;
	_cdnProduct: any;
	_menuCategory: CategoryInterface[];
	_bannerSlides: any;
	_newProducts: ProductInterface[];
	_classColumn: any;
	_cartCount: any;
	topRightMenu: any;
	_showBlank: boolean = false;
	_showSkeleton: boolean = true;

	_getAppPassingData: any;

	errorText: string;
	loadText: string;
	
  constructor(
		private api: RequestApiProvider, 
		private online: Online,
		public navParams: NavParams, 
		public notif: NotifyProvider, 
		public toastCtrl: ToastController, 
		public platform: Platform, 
		public menuProv: MenuProvider,
		public navCtrl: NavController, 
		public translate: TranslateService,
		public events: Events, 
		public smartAudio: SmartAudioProvider) 
  {
		this.dir = platform.dir();
		this.translate.get(['ERROR', 'PROCESS_MESSAGE']).subscribe((value)=>{
			this.errorText =  value.ERROR;
			this.loadText = value.PROCESS_MESSAGE;
		})

		this.events.subscribe('home:appdata', (data)=>{
			this.clickPassingData(data)
		})
	}

	clickPassingData(data)
	{
		//this.navParams.data.loadDataAppHome = undefined;
		this._getAppPassingData = data;
	}

	ngAfterViewInit() 
	{
		this._classColumn = this.platform.width() > 900 ? 'col col-big' : 'col';
		//setTimeout(() => {}, 250);
	}

	ionViewDidEnter()
	{
		const getAppData = this._getAppPassingData ? this._getAppPassingData.decision : undefined;
		//console.log(getAppData)
		if(getAppData != false)
		{
			this.getAppData()
		}
	}

	loadCountNotif(valnot)
	{
		this.notif.store(valnot, 'bell').then((val)=>{
			if(val.orinot < valnot)
			{
				this.smartAudio.play('order')
			}
		})
		this.topRightMenu = this.menuProv.topBarMenu({bell:valnot},'home');
	}

	swipe(event) 
	{
		if(event.direction === 2) 
		{
			this.navCtrl.parent.select(1);
		}
	}

	async getAppData()
	{
		this._showSkeleton = true;
    	let toastHome = this.toastCtrl.create({
			duration: 1000,
			message: this.loadText,
			cssClass: 'info-toast',
			position: 'top'
		});
		toastHome.present();
		const endpoint = 'homeApp';
		await this.api.post('app', endpoint, {init:'app-home', pack:{fieldForm:{notif_message:false}}})
		.subscribe((res: any) => {
			this._showBlank = false;
			const home = res[endpoint].items;
			this._cdnBanner = home.server.banner;
			this._cdnIcon = home.server.icon;
			this._cdnProduct = home.server.product;
			this._menuCategory = home.menuCategory;
			this._bannerSlides = home.banner;
			this._newProducts = home.product;
			const notifCount = home.notif == null ? 0 : home.notif.count;
			this.loadCountNotif(notifCount)
			this._showSkeleton = false;
		},
		(err) => {
			this._showBlank = true;
			let toastError = this.toastCtrl.create({
				duration: 6000,
				message: this.errorText,
				position: 'top'
			});
			toastError.present();
			this.online.checkOnline(false);
			this._showSkeleton = false;
		})
	}

	openPage(page: CategoryInterface)
	{
		this.navCtrl.push(page.component,
			{
				paramRoots:
				{
					id:page.id,
					slug:page.slug,
					name:page.name,
					component:page.component,
					server:this._cdnIcon
				}
			},
			{
			animate:true,
			direction: 'enter'
			}
		);
  	}
}
