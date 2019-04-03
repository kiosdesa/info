import { Component, ViewChild } from '@angular/core';
import { IonicPage, NavController, Navbar, NavParams, LoadingController, MenuController, ToastController, Platform, App, Events } from 'ionic-angular';
import { TranslateService } from '@ngx-translate/core';
import { MenuProvider, RequestApiProvider, NotifyProvider, Online } from './../../providers/providers';

@IonicPage()
@Component({
  selector: 'page-product-detail',
  templateUrl: 'product-detail.html',
})
export class ProductDetailPage 
{
  @ViewChild(Navbar) navBar: Navbar;

  dir: string = 'ltr';
  loopMedalLevel: any;
  returnInteract: boolean = false;
  favorited: boolean = false;
  followed: boolean = false;
  topRightMenu: any;
  _dataProduct: any;
  _dataSeller: any;
  _dataOtherProduct: any;
  _dataQuantity: number = 0;
  _dataMinimumOrder: number = 0;
  _dataStock: number = 0;
  _readyOrder: boolean = false;
  _cdnSeller: any;
  _cdnIcon: any;
  _cdnProduct: any;
  _loopMedalLevel: number = 4;
  _paramRoots: any;
  _showBlank: any = false;
  _dataPageCategory: any;
  _cartCount: number = 0;
  _classColumn: any;
  _nuAing: boolean = false;
  errorText: string;

  _bell_notif:any;
  _cart_notif:any;

  constructor(
    private online: Online, 
    private ReqApi: RequestApiProvider, 
    public notif: NotifyProvider, 
    public platform: Platform, 
    public events: Events, 
    public menuProv: MenuProvider, 
    public app: App, 
    public translate: TranslateService, 
    public navCtrl: NavController, 
    public menuCtrl: MenuController, 
    public loading: LoadingController,
    public toast: ToastController,
    public navParams: NavParams) 
  {
		this.dir = platform.dir();
    let _paramRoots = this.navParams.get('paramRoots');
    this._paramRoots = _paramRoots;
    this.translate.get('ERROR').subscribe((value)=>{
      this.errorText = value;
    })
  }

	ngAfterViewInit() 
	{
    this.loadCountCart();
		this._classColumn = this.platform.width() > 900 ? 'col col-big' : 'col';
	}

  ionViewDidLoad()
  {
    const slug = this._paramRoots ? 'slug' in this._paramRoots ? this._paramRoots.slug : null : null
    this.detailProduct(slug);
    this.navBar.backButtonClick = ()=>
    {
      this.navCtrl.pop().then(()=>{
        this.loadCountCart()
        this.events.publish('tab:count')
      })
    };
  }

  ionViewDidEnter() 
  {
    if(this.navCtrl.canGoBack())
    {
      this.loadCountCart()
    }
    this.menuCtrl.swipeEnable(false);
  }

  ionViewWillLeave() 
  {
    this.menuCtrl.swipeEnable(true);
  }

  refresh(refresher)
  {
    setTimeout(()=>{
      const slug = this._paramRoots ? 'slug' in this._paramRoots ? this._paramRoots.slug : null : null
			this.detailProduct(slug);
      refresher.complete();
    }, 2000);
  }

  loadCountCart()
  {
    this.notif.get('bell').then(val=>{this._bell_notif = val})
    this.notif.get('cart').then(val=>{this._cart_notif = val})
    setTimeout(()=>{this.setNotif()}, 500)
  }

  setNotif(cart?:any,bell?:any)
  {
    const _cart = cart ? cart : this._cart_notif;
    const _bell = bell ? bell : this._bell_notif;
    this.topRightMenu = this.menuProv.topBarMenu({cart:_cart, bell:_bell});
  }

  async detailProduct(param?: any)
  {
    let endpoint = 'detailProduct';
    let loader = this.loading.create({
      spinner: 'dots',
      content: 'Loading...',
    });
    loader.present();
    await this.ReqApi
    .post('sensus', endpoint, {init:'product-detail', pack:{fieldForm:{filter:param}}}).subscribe((res:any)=>{
      let theRes = res[endpoint];
      let theItems = theRes.items;
      let theProduct = theItems.product[0];
      this._nuAing = theItems.nu_aing;
      this._readyOrder = theProduct.interact_data.ready_order;
      this._dataProduct = theItems.product;
      this._dataSeller = theItems.product[0].seller_detail;
      this._dataOtherProduct = theItems.other_product;
      this._dataQuantity = theProduct.minimum_order;
      this._dataMinimumOrder = theProduct.minimum_order;
      this._dataStock = theProduct.stock;
			this._cdnSeller = theItems.server.seller;
			this._cdnIcon = theItems.server.icon;
      this._cdnProduct = theItems.server.product;
      this._showBlank = false;
      this._dataPageCategory = {
        slug:theProduct.category_detail.slug, 
        name:theProduct.category_detail.name, 
        component:'ProductCategoryPage'
      };
      this._loopMedalLevel = theProduct.seller_detail.score.medal;
      this.favorited = theProduct.favorited;
      this.followed = theProduct.followed;
      //console.log(this._dataProduct)
      loader.dismiss();
    },
		(err)=>{
      this._showBlank = true;
      this._readyOrder = false;
      loader.dismiss();
      this.online.checkOnline(false);
    })
  }

	shareProduct(param?:any)
	{
    let params = param ? param : this._dataProduct;
    let slug = params[0].slug;
    let url = 'https://bumdesku.com/product/'+slug;
		console.log('Share ' + url)
	}

	async favProduct(param?:any, status?:any)
	{
    let params = param ? param : this._dataProduct,
    paramsObject = params[0].interact_data;
    paramsObject['favorited'] = status ? status : this.favorited;
    let cluster = 'cabinet';
    let endpoint = 'favoriteProduct';
    let loader = this.loading.create({
      spinner: 'dots',
      content: 'Loading...',
    });
    loader.present();
    await this.ReqApi
    .post(cluster, endpoint, {init:'product-favorite', pack:{fieldForm:paramsObject}})
    .subscribe((res:any)=>{
      let theRes = res[endpoint];
      let theItems = theRes.items;
      if(theItems.approve == true)
      {
        this.favorited = theItems.favoriting;
				let toast = this.toast.create({
					message: theItems.message,
					duration: 3000,
					position: 'top'
				});
				toast.present();
        this.returnInteract = true;
      }
      loader.dismiss();
    },
		(err)=>{
      loader.dismiss();
      this.online.checkOnline(false);
    })
	}

	async buyProduct(qty?:any)
	{
    this.addCart(qty).then(()=>{
      this.goToNextTab({index:2})
    })
	}

	async addCart(qty?:any)
	{
    let param = this._dataProduct,
    paramsObject = param[0].interact_data;
    paramsObject['product_qty'] = qty;
    let paramData = {init:'carts-add', pack:{fieldForm:paramsObject}};
    
		let endpoint = 'addCarts';
    let loader = this.loading.create({
      spinner: 'dots',
      content: 'Loading...'
    });
    loader.present();
		await this.ReqApi.post('transaction',endpoint, paramData)
		.subscribe((res:any)=>{
			let Resp = res[endpoint];
			let theItems = Resp.items;
      if(theItems.approve == true)
      {
				let toast = this.toast.create({
					message: theItems.message,
          cssClass: 'success-toast',
					duration: 3000,
					position: 'bottom'
				});
				toast.present();
        this.notif.store(theItems.count, 'cart');
        this.setNotif(theItems.count)
      }
      loader.dismiss();
		}, (err)=>{
      loader.dismiss();
			this.online.checkOnline(false);
		})
	}

	async followSeller(status?:any)
	{
    let paramsObject = this._dataProduct[0].interact_data;
    paramsObject['followed'] = status ? status : this.followed;
    let cluster = 'cabinet';
    let endpoint = 'followingSeller';
    let loader = this.loading.create({
      spinner: 'dots',
      content: 'Loading...',
    });
    loader.present();
    await this.ReqApi
    .post(cluster, endpoint, {init:'seller-following', pack:{fieldForm:paramsObject}})
    .subscribe((res:any)=>{
      let theRes = res[endpoint];
      let theItems = theRes.items;
      if(theItems.approve == true)
      {
        this.followed = theItems.followed;
				let toast = this.toast.create({
					message: theItems.message,
					duration: 3000,
					position: 'top'
				});
				toast.present();
      }
      loader.dismiss();
    },
		(err)=>{
      loader.dismiss();
      this.online.checkOnline(false);
    })
	}

	chatSeller(param?:any)
	{
    let params = param ? param : this._dataProduct,
    paramsObject = params[0].interact_data;
    this.navCtrl.push('ChatPage', 
      {paramRoots:{title:paramsObject.seller_name}}, 
      {animate:true}
    )
		//console.log('Chat ', paramsObject)
  }
  
  // Mengurangi jumlah pembelian (add cart)
  decrementQty()
  {
    if(this._dataQuantity > this._dataMinimumOrder) this._dataQuantity--;
  }
  // Menambah jumlah pembelian (add cart)
  incrementQty()
  {
    if(this._dataQuantity < this._dataStock) this._dataQuantity++;
  }

  // Meloncat ke halaman keranjang (Cart list)
  goToNextTab(tabs)
  {
    if(tabs.index == undefined)
    {
      this.openPage(tabs);
    }
    else
    {
      this.events.publish('tab:clicked',{tab:tabs.index})
      if(this.navCtrl.canGoBack())
      {
        this.navCtrl.popToRoot().then(()=>{})
      }
    }
  }

	openPage(param)
	{
		this.navCtrl.push(param.component,
			{
				paramRoots:
				{
					slug:param.slug,
					name:param.name,
					component:param.component
				}
			},
			{
			animate:true,
			direction: 'enter'
			}
		);
  }

	pushPage(page, param?:any)
	{
    param = param ? {paramRoots:param} : {}
		this.navCtrl.push(page, param,{animate:true,direction:'enter'});
  }

	openRootPage(p)
	{ 
    this.navCtrl.popToRoot().then(()=>{
      this.openPage(p)
    })
	}
}