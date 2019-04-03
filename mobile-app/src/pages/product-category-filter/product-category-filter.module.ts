import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';
import { ProductCategoryFilterPage } from './product-category-filter';

@NgModule({
  declarations: [
    ProductCategoryFilterPage,
  ],
  imports: [
    IonicPageModule.forChild(ProductCategoryFilterPage),
    TranslateModule.forChild()
  ],
})
export class ProductCategoryFilterPageModule {}
