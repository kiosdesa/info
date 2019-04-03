import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';
import { ProductCategoryPage } from './product-category';

@NgModule({
  declarations: [
    ProductCategoryPage,
  ],
  imports: [
    IonicPageModule.forChild(ProductCategoryPage),
    TranslateModule.forChild()
  ],
})
export class ProductCategoryPageModule {}
