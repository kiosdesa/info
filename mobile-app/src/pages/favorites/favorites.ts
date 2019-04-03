import { Component } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { IonicPage, NavController, LoadingController, ToastController,  NavParams, App, Platform } from 'ionic-angular';
import { MenuProvider, RequestApiProvider, NotifyProvider, Online } from './../../providers/providers';
import { Storage } from '@ionic/storage';

export interface ProductInterface {
	id: any;
  name: string;
  slug: string;
  thumb?: string;
  component?: any;
	fix_price?: string;
}

@IonicPage()
@Component({
  selector: 'page-favorites',
  templateUrl: 'favorites.html',
})
export class FavoritesPage 
{
	topRightMenu: any;
	thisToken: any = undefined;
	_listData: any;
	_showBlank: boolean = false;
	_classColumn: any;
	_cdnProduct: any;
	_countBell: any = 0;
	_paramSearch: string = undefined;

	errorText: string;
	notFoundText: string;
	
  constructor(
		private app: App,
		private storage: Storage, 
		private online: Online, 
		private menuProv: MenuProvider, 
    private notif: NotifyProvider, 
    public translate: TranslateService, 
		public toast: ToastController, 
		public platform: Platform, 
		public loading: LoadingController, 
		public api: RequestApiProvider, 
    public navCtrl: NavController, 
    public navParams: NavParams)
  {
    this.storage.get('loginToken').then((isToken) => {
      if(isToken)
      {
        this.thisToken = isToken;
      }
    })
    this.translate.get(['ERROR', 'FAVORITE_SEARCH_FAILED']).subscribe((value)=>{
			this.errorText = value.ERROR;
			this.notFoundText = value.FAVORITE_SEARCH_FAILED;
    })
	}
	
	loadNotif()
	{
    this.notif.get('bell').then(val=>{
			this.topRightMenu = this.menuProv.topBarMenu({bell:val},'notif');
		})
	}

	ngAfterViewInit() 
	{
		this._classColumn = this.platform.width() > 900 ? 'col col-big' : 'col';
	}

	ionViewDidEnter()
	{
		this.listFavorite()
		this.loadNotif()
	}

  search()
	{}
	
	async listFavorite()
	{
		let endpoint = 'favoritelistProduct';
    let loader = this.loading.create({
      spinner: 'dots',
      content: 'Loading...'
    });
    loader.present();
		await this.api
    .post('sensus', endpoint, {init:'product-favoritelist'})
    .subscribe((res: any)=>{
			let Resp = res[endpoint];
			let theItems = Resp.items;
      if(theItems.approve == true)
      {
				this._listData = theItems.data;
				this._cdnProduct = theItems.server.product;
				this._showBlank = false;
      }
      else
      {
				this._showBlank = true;
      }
      loader.dismiss();
		}, (err)=>{
      this._showBlank = true;
      loader.dismiss();
			let toast = this.toast.create({
				message: this.errorText,
				cssClass: 'danger-toast',
				duration: 3000,
				position: 'top'
			});
			toast.present();
			this.online.checkOnline(false);
		})
	}

	async unfavProduct(index:number, param?:any)
	{
		let paramReformat = {};
    paramReformat['id_product'] = param['id'];
    paramReformat['slug_product'] = param['slug'];
    paramReformat['id_seller'] = param['seller_id'];
    paramReformat['token'] = this.thisToken;
    paramReformat['favorited'] = true;
    let cluster = 'cabinet';
    let endpoint = 'favoriteProduct';
    let loader = this.loading.create({
      spinner: 'dots',
      content: 'Loading...',
    });
    loader.present();
    await this.api
    .post(cluster, endpoint, {init:'product-favorite', pack:{fieldForm:paramReformat}})
    .subscribe((res: any) => {
      let theRes = res[endpoint];
      let theItems = theRes.items;
      if(theItems.approve == true)
      {
				if(index > -1) this._listData.splice(index, 1);
				let toast = this.toast.create({
					message: theItems.message,
					duration: 3000,
					position: 'top'
				});
				toast.present();
      }
      loader.dismiss();
    },
		(err) => {
      loader.dismiss();
      this.online.checkOnline(false);
    })
	}

	async searchFavorite(field?:any)
	{
		const endpoint: string = 'favoritesearchProduct';
    let loader = this.loading.create({
      spinner: 'dots',
      content: 'Loading...'
    });
		let toast = this.toast.create({
			message: '"' + field + '" ' + this.notFoundText,
			cssClass: 'warning-toast',
			duration: 3000,
			position: 'top'
		});
		loader.present();
		
		const param = {search:field};
		await this.api
    .post('sensus', endpoint, {init:'product-favoritesearch', pack:{fieldForm:param}})
    .subscribe((res: any)=>{
			let Resp = res[endpoint];
			let theItems = Resp.items;
      if(theItems.approve == true)
      {
				this._listData = theItems.data;
				this._cdnProduct = theItems.server.product;
				this._showBlank = false;
      }
      else
      {
				toast.present();
				this._showBlank = true;
      }
      loader.dismiss();
		}, 
		(err)=>{
      this._showBlank = true;
			loader.dismiss();
			toast.present();
			this.online.checkOnline(false);
		})
	}

  searchTyping(e)
  {
    let val = e.target.value;
    this._paramSearch = val;
  }

  searchPress(key)
  {
    if(key.charCode == 13)
    {
      if(this._paramSearch == undefined || this._paramSearch == '')
      {
				this.listFavorite(); 
      }
      else
      {
        if(this._paramSearch.length > 3)
        {
					this.searchFavorite(this._paramSearch)
        }
        else
        {
					this.listFavorite(); 
        }
      }
    }
  }

	swipe(event) 
	{
		if(event.direction === 2) 
		{
			this.navCtrl.parent.select(2);
		}
		if(event.direction === 4) 
		{
			this.navCtrl.parent.select(0);
		}
	}

	openPage(p)
	{
    this.app.getRootNav().setRoot(p,{paramRoots:p.param},{
			animate:true,
			direction: 'enter'
		});
	}

	openProduct(page: ProductInterface)
	{
		this.app.getRootNav().push(page.component,
			{
				paramRoots:
				{
					id:page.id,
					slug:page.slug,
					name:page.name,
					component:page.component
				}
			},
			{
			animate:true,
			direction: 'enter'
			}
		);
  }
  
  pushPage(page)
  {
		this.app.getRootNav().push(page.component,
			{
				paramRoots:
				{
					slug:page.slug,
					name:page.name,
					component:page.component
				}
			},
			{
			animate:true,
			direction: 'enter'
			}
		);
  }
}
