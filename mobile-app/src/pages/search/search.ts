import { Component } from '@angular/core';
import { IonicPage, NavController, MenuController, ModalController, NavParams, Platform, App, LoadingController, Events } from 'ionic-angular';
import { MenuProvider, ProductProvider, Online } from './../../providers/providers';
//import { ProductCategoryModalPage, ProductCategoryFilterPage } from './../../pages/pages';

export interface ProductInterface 
{
	id: any;
  name: string;
  slug: string;
  thumb?: string;
  component?: any;
	fix_price?: string;
	sku?: string;
	returned?: string;
	flash_sale?: string;
  seller_id?: any;
  seller_detail?: any;
}

@IonicPage()
@Component({
  selector: 'page-search',
  templateUrl: 'search.html'
})
export class SearchPage 
{
  _listData: ProductInterface[];
  _classColumn: any;
  _cdnSeller: any;
  _cdnIcon: any;
  _cdnProduct: any;
  _countProduct: number = undefined;
  _paramSearch: string = undefined;
  _rangePrice: any = {};
  _fieldForm: any = {};
  _showBlank: boolean = false;

  constructor(
    public app: App, 
    public online: Online, 
    public navCtrl: NavController, 
    public navParams: NavParams,
    public platform: Platform, 
    public menuCtrl: MenuController,
    public events: Events,
    public modalCtrl: ModalController,
    public loading: LoadingController,
    public menuProv: MenuProvider,
    public productProv: ProductProvider) 
  {}

	ngAfterViewInit() 
	{
		this._classColumn = this.platform.width() > 900 ? 'col col-big' : 'col';
	}
  
  ionViewDidEnter() 
  {
    this.menuCtrl.swipeEnable(false);
  }

  ionViewWillLeave() 
  {
    this.menuCtrl.swipeEnable(true);
  }

  searchTyping(e)
  {
    let val = e.target.value;
    this._paramSearch = val;
  }

  searchPress(key)
  {
    // Deteksi tombol enter
    if(key.charCode == 13)
    {
      // Hapus index search pada object fieldform jika param search kosong atau undefined
      if(this._paramSearch == undefined || this._paramSearch == '')
      {
        if("search" in this._fieldForm) delete this._fieldForm.search;
      }
      else
      {
        // tambahkan index search pada object fieldform jika param search terdapat teks
        if(this._paramSearch.length > 3)
        {
          this._fieldForm['search'] = this._paramSearch;
        }
        else
        {
          // Hapus index search pada object fieldform jika param search kurang dari 3 karakter
          if("search" in this._fieldForm) delete this._fieldForm.search;
        }
      }

      this.getProduct('lookup', {
        init:'product-lookup', 
        pack:{
          fieldForm:this._fieldForm, filterType:null
        }
      })
    }
  }

  async getProduct(endpoint, param?: any)
  {
    let loader = this.loading.create({
      spinner: 'dots',
      content: 'Loading...',
    });
    loader.present();
    await this.productProv.filter(endpoint, param).subscribe((res: any) => {
      let theRes = res[endpoint + 'Product'];
      let theItems = theRes.items;
      this._listData = theItems.product;
      this._rangePrice = theItems.range_price;
			this._cdnSeller = theItems.server.seller;
			this._cdnIcon = theItems.server.icon;
      this._cdnProduct = theItems.server.product;
      this._countProduct = theItems.total;
      this._showBlank = false;
      loader.dismiss();
    },
		(err) => {
      this._showBlank = true;
      loader.dismiss();
      this.online.checkOnline(false);
    })
  }

  openPage(page)
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

	openProduct(page: ProductInterface)
	{
		this.navCtrl.push(page.component,
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
}
