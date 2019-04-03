import { Component } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { IonicPage, NavController, ToastController, LoadingController, AlertController, NavParams, Platform, Events } from 'ionic-angular';
import { MenuProvider, RequestApiProvider, Online, NotifyProvider } from './../../providers/providers';

export interface ProductInterface {
	id: any;
	name: string;
	slug: string;
	thumb?: string;
	component?: any;
	fix_price?: string;
}

export interface DataProductInterface {
	id: number;
	fix_price: any;
	minimum_order: number;
	name: string;
	quantity: number;
	slug: string;
	stock: number;
	thumb: string;
	note: string;
	weight_gram: number;
}
type DataProduct = DataProductInterface[];

@IonicPage()
@Component({
  selector: 'page-carts',
  templateUrl: 'carts.html',
})
export class CartsPage
{
	topRightMenu: any;
	_classColumn: any;
	_cdnSeller: any;
	_cdnIcon: any;
	_cdnProduct: any;
	_dataCart: any = undefined;
	_dataProductCart: DataProduct;
	_showBlank: boolean = false;
	_countBell: any = 0;
	_cartCount: any = 0;
	_totalPrice: any = 0;
	_currencySymbol: any;
	_allButtonStatus: boolean = false;

	errorText: string;
	DeleteTitle: string;
	DeleteMessage: string;
	DeleteButton: string;
	CancelButton: string;
	FavoriteButton: string;
  
	constructor(
		private translate: TranslateService, 
		private menuProv: MenuProvider, 
		private api: RequestApiProvider, 
		private online: Online, 
		public notif: NotifyProvider, 
		public platform: Platform, 
		public events: Events, 
		public navCtrl: NavController, 
		public toast: ToastController, 
		public loading: LoadingController, 
		public alert: AlertController, 
		public navParams: NavParams) 
	{
    	this.translate.get(['ERROR', 'DELETE_TITLE', 'DELETE_MESSAGE', 'DELETE_BUTTON', 'CANCEL_BUTTON', 'FAVORITE_ADD']).subscribe((value) => {
			this.errorText = value.ERROR;
			this.DeleteTitle = value.DELETE_TITLE;
			this.DeleteButton = value.DELETE_BUTTON;
			this.DeleteMessage = value.DELETE_MESSAGE;
			this.CancelButton = value.CANCEL_BUTTON;
			this.FavoriteButton = value.FAVORITE_ADD;
		});
	}

	ngAfterViewInit() 
	{
		this._classColumn = this.platform.width() > 900 ? 'col col-big' : 'col';
	}
	
	ionViewDidEnter()
	{
		this.loadCart({init:'carts-lists', pack:{fieldForm:{seller_detail:true}}})
		this.loadNotif()
	}

	loadNotif()
	{
    	this.notif.get('bell').then(val=>{
			this.topRightMenu = this.menuProv.topBarMenu({bell:val},'notif');
		})
	}

	refresh(refresher)
	{
		setTimeout(() => {
			this.loadCart({init:'carts-lists', pack:{fieldForm:{seller_detail:true}}});
			refresher.complete();
		}, 2000);
	}

	async loadCart(param)
	{
		let load = param.hasOwnProperty('form_cart') ? param.form_cart : false;
		let endpoint = 'listsCarts';
		let loader = this.loading.create({
			spinner: 'dots',
			content: 'Loading...'
		});

		if(load == false) loader.present();

		await this.api.post('transaction', endpoint, param)
		.subscribe((res:any)=>{
			this._allButtonStatus = false;
			let Resp = res[endpoint];
			let theItems = Resp.items;
			if(load == false) loader.dismiss();
			if(theItems.approve == true)
			{
				this._showBlank = false;
				this._dataCart = theItems.data;
				this._cdnSeller = theItems.server.seller;
				this._cdnProduct = theItems.server.product;
				this._cdnIcon = theItems.server.icon;
				this._cartCount = theItems.count;
				this._totalPrice = theItems.total_price;
				this.notif.store(theItems.count, 'cart');
			}
			else
			{
				this._dataCart = undefined;
				this.notif.store(null, 'cart');
				this._showBlank = true;
			}
		}, (err)=>{
			this._dataCart = undefined;
			this.notif.store(null, 'cart');
			this._showBlank = true;
      		if(load == false) loader.dismiss();
			this.online.checkOnline(false);
		})
	}

	checkout(p)
	{
		this.openPage(p)
	}

	async deleteItems(param)
	{
		let endpoint = 'removeCarts';
		let loader = this.loading.create({
			spinner: 'dots',
			content: 'Loading...'
		});
		loader.present();
		let params = {
		init:'carts-remove',pack:{fieldForm:param}};
		await this.api.post('transaction',endpoint,params)
		.subscribe((res:any)=>{
			let Resp = res[endpoint];
			let theItems = Resp.items;
			loader.dismiss();
			if(theItems.approve == true)
			{
				let toast = this.toast.create({
					message: theItems.message,
					cssClass: 'success-toast',
					duration: 3000,
					position: 'top'
				});
				toast.present();
				this.loadCart({init:'carts-lists', pack:{fieldForm:{seller_detail:true}}});
			}
			else
			{
				let toast = this.toast.create({
					message: theItems.message,
					cssClass: 'danger-toast',
					duration: 3000,
					position: 'top'
				});
				toast.present();
			}
		}, (err)=>{
			loader.dismiss();
			this.online.checkOnline(false);
		})
	}

	remove(param?:any)
	{
		let alert = this.alert.create({
			cssClass: 'no-scroll',
			title: this.DeleteTitle,
			message: this.DeleteMessage,
			buttons: [
			{
				text: this.DeleteButton,
				handler: () => {
					this.deleteItems(param)
				}
			},
			{
				text: this.DeleteButton + ' & ' + this.FavoriteButton,
				handler:()=>{
					let endpoint = 'favoriteProduct';
					let loader = this.loading.create({
						spinner: 'dots',
						content: 'Loading...',
					});
					loader.present();
					this.api
					.post('cabinet', endpoint, {init:'product-favorite', pack:{fieldForm:param}})
					.subscribe((res:any)=>{
						loader.dismiss();
						let theRes = res[endpoint];
						let theItems = theRes.items;
						if(theItems.approve == true)
						{
							let toast = this.toast.create({
								message: theItems.message,
								cssClass: 'success-toast',
								duration: 3000,
								position: 'top'
							});
							toast.present();
						}
						else
						{
							let toast = this.toast.create({
								message: theItems.message,
								cssClass: 'warning-toast',
								duration: 3000,
								position: 'top'
							});
							toast.present();
						}
						setTimeout(()=>{
							this.deleteItems(param)
						},3000)
					},
					(err)=>{
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
			},
			{
				text: this.CancelButton,
				role: 'cancel',
				handler: () => {}
			}]
		});
		alert.present();
	}

	async update(param?:any)
	{
		this._allButtonStatus = true;
		let paramObject = param;
		paramObject['load'] = true;
		paramObject['form_cart'] = true;
    	let paramData = {init:'carts-update', pack:{fieldForm:paramObject}}; 
		let endpoint = 'updateCarts';
		await this.api.post('transaction',endpoint,paramData)
		.subscribe((res:any)=>{
			this._allButtonStatus = false;
			let Resp = res[endpoint];
			let theItems = Resp.items;
			if(theItems.approve == true)
			{
				this.loadCart({init:'carts-lists', pack:{fieldForm:{seller_detail:true}}, form_cart:paramObject['form_cart']});
			}
			else
			{
				let toast = this.toast.create({
					message: theItems.message,
          			cssClass: 'danger-toast',
					duration: 3000,
					position: 'top'
				});
				toast.present();
			}
		}, (err)=>{
			this._allButtonStatus = false;
			this.online.checkOnline(false);
		})
	}
  
	decrementQty(indexparent,indexroot)
	{
		this._allButtonStatus = true;
		if(this._dataCart[indexparent].data_product[indexroot].quantity > this._dataCart[indexparent].data_product[indexroot].minimum_order) 
		{
			this._dataCart[indexparent].data_product[indexroot].quantity--;
			this.update(
				{
					id_seller:this._dataCart[indexparent].seller_id,
					id_product:this._dataCart[indexparent].data_product[indexroot].id,
					product_qty:this._dataCart[indexparent].data_product[indexroot].quantity,
					ready_order:this._dataCart[indexparent].data_product[indexroot].ready_order,
					token_cart:this._dataCart[indexparent].token_cart
				}
			);
		}
	}
	
	incrementQty(indexparent,indexroot)
	{
		this._allButtonStatus = true;
		if(this._dataCart[indexparent].data_product[indexroot].quantity < this._dataCart[indexparent].data_product[indexroot].stock) 
		{
			this._dataCart[indexparent].data_product[indexroot].quantity++;
			this.update(
				{
					id_seller:this._dataCart[indexparent].seller_id,
					id_product:this._dataCart[indexparent].data_product[indexroot].id,
					product_qty:this._dataCart[indexparent].data_product[indexroot].quantity,
					ready_order:this._dataCart[indexparent].data_product[indexroot].ready_order,
					token_cart:this._dataCart[indexparent].token_cart
				}
			);
		}
	}

	changeNote(indexparent,indexroot,text)
	{
		this.update(
			{
				id_seller:this._dataCart[indexparent].seller_id,
				id_product:this._dataCart[indexparent].data_product[indexroot].id,
				token_cart:this._dataCart[indexparent].token_cart,
				note:text
			}
		);
	}

	swipe(event) 
	{
		if(event.direction === 2) 
		{
			this.navCtrl.parent.select(3);
		}
		if(event.direction === 4) 
		{
			this.navCtrl.parent.select(1);
		}
	}

	openPage(p)
	{
		this.navCtrl.push(p.page,{paramRoots:p.param},
		{
			animate:true,
			direction: 'enter'
		});
	}

	openProduct(page: ProductInterface)
	{
		this.navCtrl.push('ProductDetailPage',
		{
			paramRoots:
			{
				id:page.id,
				slug:page.slug,
				name:page.name,
				component:'ProductDetailPage'
			}
		},
		{
			animate:true,
			direction: 'enter'
		});
  	}

  	pushPage(page)
  	{
		this.navCtrl.push(page.component,
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