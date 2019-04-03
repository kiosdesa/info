import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams, LoadingController, ToastController, Platform } from 'ionic-angular';
import { RequestApiProvider, Online, NotifyProvider, MenuProvider } from './../../providers/providers';

export interface ProductInterface {
	id: any;
  name: string;
  slug: string;
  thumb?: string;
	fix_price?: string;
}

@IonicPage()
@Component({
  selector: 'page-seller-detail',
  templateUrl: 'seller-detail.html',
})
export class SellerDetailPage 
{
  _paramRoots: any;
  _dataSeller: any;
  _dataSellerIndex: any;
  _showBlank = false;
  _classColumn: any;
  _nuAing: boolean = false;

  _cdnSeller: any;
  _cdnIcon: any;
  _cdnProduct: any;

	topRightMenu: any;
  followed: boolean = false;

  constructor(
    private online: Online, 
    private notif: NotifyProvider, 
		private menuProv: MenuProvider, 
    public platform: Platform, 
    public reqapiprov: RequestApiProvider, 
    public navCtrl: NavController, 
    public navParams: NavParams,
    public loading: LoadingController,
    public toast: ToastController) 
  {
    let _paramRoots = this.navParams.get('paramRoots');
    this._paramRoots = _paramRoots;
  }

	ngAfterViewInit() 
	{
		this._classColumn = this.platform.width() > 900 ? 'col col-big' : 'col';
	}

  ionViewDidLoad() 
  {
    this.loadSeller()
  }

	ionViewDidEnter()
	{
		this.loadNotif()
	}

	loadNotif()
	{
    this.notif.get('bell').then(val=>{
			this.topRightMenu = this.menuProv.topBarMenu({bell:val},'notif');
		})
  }
  
  async loadSeller()
  {
    let endpoint = 'detailSeller';
    let param = {init:'seller-detail', pack:{fieldForm:{slug:this._paramRoots['slug']}}};
    let loading = this.loading.create({
      spinner: 'dots',
      content: 'Loading...',
    });
    loading.present();
    await this.reqapiprov.post('account', endpoint, param)
    .subscribe((res:any)=>{
      let Resp = res[endpoint];
      let theItems = Resp.items;
      let theSeller = theItems.seller[0];
      this._dataSeller = theItems.seller;
      this._dataSellerIndex = theSeller;
      this._nuAing = theItems.nu_aing;
      this._cdnSeller = theItems.server.seler;
      this._cdnIcon = theItems.server.icon;
      this._cdnProduct = theItems.server.product;
      this.followed = theSeller.followed;
      this._showBlank = false;
      loading.dismiss();
    }, 
		(err) => {
      this._showBlank = true;
      loading.dismiss();
      this.online.checkOnline(false);
    })
  }

	async followSeller()
	{
    let paramsObject = this._dataSellerIndex.interact_data;
    paramsObject['followed'] = status ? status : this.followed;
    let cluster = 'cabinet';
    let endpoint = 'followingSeller';
    let loader = this.loading.create({
      spinner: 'dots',
      content: 'Loading...',
    });
    loader.present();
    await this.reqapiprov
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
    let params = param ? param : this._dataSeller,
    paramsObject = params[0].interact_data;
    this.navCtrl.push('ChatPage', 
      {paramRoots:{title:paramsObject.seller_name}}, 
      {animate:true}
    )
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
			}
		);
  }

  pushPage(page, param)
  {
    param = param ? {paramRoots:param} : {}
    this.navCtrl.push(page, param, {animate:true, direction:'enter'})
  }
}